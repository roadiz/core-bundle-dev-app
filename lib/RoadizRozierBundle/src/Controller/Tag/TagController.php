<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Tag;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\Core\Handlers\HandlerFactoryInterface;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\CoreBundle\Entity\TagTranslation;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\EntityHandler\TagHandler;
use RZ\Roadiz\CoreBundle\Event\Tag\TagCreatedEvent;
use RZ\Roadiz\CoreBundle\Event\Tag\TagDeletedEvent;
use RZ\Roadiz\CoreBundle\Event\Tag\TagUpdatedEvent;
use RZ\Roadiz\CoreBundle\Exception\EntityAlreadyExistsException;
use RZ\Roadiz\CoreBundle\Form\Error\FormErrorSerializer;
use RZ\Roadiz\CoreBundle\ListManager\EntityListManagerFactoryInterface;
use RZ\Roadiz\CoreBundle\Repository\TranslationRepository;
use RZ\Roadiz\CoreBundle\Security\LogTrail;
use RZ\Roadiz\RozierBundle\Controller\VersionedControllerTrait;
use RZ\Roadiz\RozierBundle\Form\TagTranslationType;
use RZ\Roadiz\RozierBundle\Form\TagType;
use RZ\Roadiz\RozierBundle\Widget\TreeWidgetFactory;
use RZ\Roadiz\Utils\StringHandler;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\String\UnicodeString;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
final class TagController extends AbstractController
{
    use VersionedControllerTrait;

    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly FormErrorSerializer $formErrorSerializer,
        private readonly HandlerFactoryInterface $handlerFactory,
        private readonly TreeWidgetFactory $treeWidgetFactory,
        private readonly EntityListManagerFactoryInterface $entityListManagerFactory,
        private readonly ManagerRegistry $managerRegistry,
        private readonly TranslatorInterface $translator,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly LogTrail $logTrail,
    ) {
    }

    #[\Override]
    protected function getDoctrine(): ManagerRegistry
    {
        return $this->managerRegistry;
    }

    #[\Override]
    protected function createNamedFormBuilder(string $name = 'form', mixed $data = null, array $options = []): FormBuilderInterface
    {
        return $this->formFactory->createNamedBuilder($name, FormType::class, $data, $options);
    }

    #[Route(
        path: '/rz-admin/tags',
        name: 'tagsHomePage',
        methods: ['GET'],
    )]
    public function indexAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_TAGS');

        $assignation = [];
        $listManager = $this->entityListManagerFactory->createAdminEntityListManager(
            Tag::class
        );
        $listManager->setDisplayingNotPublishedNodes(true);
        $listManager->handle();

        if ($this->isGranted('ROLE_ACCESS_TAGS_DELETE')) {
            $deleteTagsForm = $this->buildBulkDeleteForm($request->getRequestUri());
            $assignation['deleteTagsForm'] = $deleteTagsForm->createView();
        }

        $assignation['filters'] = $listManager->getAssignation();
        $assignation['tags'] = $listManager->getEntities();

        return $this->render('@RoadizRozier/tags/list.html.twig', $assignation);
    }

    /**
     * Return an edition form for current translated tag.
     */
    #[Route(
        path: '/rz-admin/tags/edit/{tagId}',
        name: 'tagsEditPage',
        requirements: ['tagId' => '\d+'],
        defaults: ['translationId' => null],
    )]
    #[Route(
        path: '/rz-admin/tags/edit/{tagId}/translation/{translationId}',
        name: 'tagsEditTranslatedPage',
        requirements: ['tagId' => '\d+', 'translationId' => '\d+'],
    )]
    public function editTranslatedAction(
        Request $request,
        #[MapEntity(
            expr: 'repository.find(tagId)',
            evictCache: true,
        )]
        Tag $tag,
        #[MapEntity(
            expr: 'translationId ? repository.find(translationId) : repository.findDefault()',
        )]
        Translation $translation,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_TAGS');
        /*
         * Here we need to directly select tagTranslation
         * if not doctrine will grab a cache tag because of TagTreeWidget
         * that is initialized before calling route method.
         */
        /** @var TagTranslation|null $tagTranslation */
        $tagTranslation = $this->managerRegistry->getRepository(TagTranslation::class)
            ->findOneBy(['translation' => $translation, 'tag' => $tag]);

        if (null === $tagTranslation) {
            /*
             * If translation does not exist, we created it.
             */
            $this->managerRegistry->getManager()->refresh($tag);
            $baseTranslation = $tag->getTranslatedTags()->first();
            $tagTranslation = new TagTranslation($tag, $translation);
            if (false !== $baseTranslation) {
                $tagTranslation->setName($baseTranslation->getName());
            } else {
                $tagTranslation->setName('tag_'.$tag->getId());
            }
            $this->managerRegistry->getManager()->persist($tagTranslation);
            $this->managerRegistry->getManager()->flush();
        }

        $assignation = [];

        /*
         * Versioning
         */
        if ($this->isGranted('ROLE_ACCESS_VERSIONS')) {
            if (null !== $response = $this->handleVersions($request, $tagTranslation, $assignation)) {
                return $response;
            }
        }

        $form = $this->createForm(TagTranslationType::class, $tagTranslation, [
            'tagName' => $tag->getTagName(),
            'disabled' => $this->isReadOnly,
        ]);
        $form->handleRequest($request);
        $isJsonRequest =
            $request->isXmlHttpRequest()
            || \in_array('application/json', $request->getAcceptableContentTypes())
        ;

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                /*
                 * Update tag slug if not locked
                 * only from default translation.
                 */
                $newTagName = StringHandler::slugify($tagTranslation->getName());
                if ($tag->getTagName() !== $newTagName) {
                    if (
                        !$tag->isLocked()
                        && $translation->isDefaultTranslation()
                        && !$this->tagNameExists($newTagName)
                    ) {
                        $tag->setTagName($tagTranslation->getName());
                    }
                }
                $this->managerRegistry->getManager()->flush();
                /*
                 * Dispatch event
                 */
                $this->eventDispatcher->dispatch(
                    new TagUpdatedEvent($tag)
                );

                $msg = $this->translator->trans('tag.%name%.updated', [
                    '%name%' => $tagTranslation->getName(),
                ]);
                $this->logTrail->publishConfirmMessage($request, $msg, $tag);

                /*
                 * Force redirect to avoid resending form when refreshing page
                 */
                if (!$isJsonRequest) {
                    return $this->getPostUpdateRedirection($tagTranslation);
                }

                return new JsonResponse([
                    'status' => 'success',
                    'errors' => [],
                ], Response::HTTP_PARTIAL_CONTENT);
            }

            /*
             * Handle errors when Ajax POST requests
             */
            if ($isJsonRequest) {
                $errors = $this->formErrorSerializer->getErrorsAsArray($form);

                return new JsonResponse([
                    'status' => 'fail',
                    'errors' => $errors,
                    'message' => $this->translator->trans('form_has_errors.check_you_fields'),
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }
        /** @var TranslationRepository $translationRepository */
        $translationRepository = $this->managerRegistry->getRepository(Translation::class);

        return $this->render('@RoadizRozier/tags/edit.html.twig', [
            ...$assignation,
            'tag' => $tag,
            'translation' => $translation,
            'translatedTag' => $tagTranslation,
            'available_translations' => $translationRepository->findAll(),
            'translations' => $translationRepository->findAvailableTranslationsForTag($tag),
            'form' => $form->createView(),
            'readOnly' => $this->isReadOnly,
        ]);
    }

    protected function tagNameExists(string $name): bool
    {
        $entity = $this->managerRegistry->getRepository(Tag::class)->findOneByTagName($name);

        return null !== $entity;
    }

    #[Route(
        path: '/rz-admin/tags/bulk-delete',
        name: 'tagsBulkDeletePage'
    )]
    public function bulkDeleteAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_TAGS_DELETE');

        if (empty($request->get('deleteForm')['tagsIds'])) {
            throw new ResourceNotFoundException();
        }

        $tagsIds = trim((string) $request->get('deleteForm')['tagsIds']);
        $tagsIds = \json_decode($tagsIds, true, flags: JSON_THROW_ON_ERROR);
        array_filter($tagsIds);

        $tags = $this->managerRegistry->getRepository(Tag::class)
            ->findBy([
                'id' => $tagsIds,
            ]);

        if (0 === count($tags)) {
            throw new ResourceNotFoundException();
        }

        $form = $this->buildBulkDeleteForm(
            $request->get('deleteForm')['referer'],
            $tagsIds
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $msg = $this->bulkDeleteTags($form->getData());

            $this->logTrail->publishConfirmMessage($request, $msg);

            if (!empty($form->getData()['referer'])) {
                return $this->redirect($form->getData()['referer']);
            } else {
                return $this->redirectToRoute('tagsHomePage');
            }
        }

        $assignation = [];
        $assignation['tags'] = $tags;
        $assignation['form'] = $form->createView();

        if (!empty($request->get('deleteForm')['referer'])) {
            $assignation['referer'] = $request->get('deleteForm')['referer'];
        }

        return $this->render('@RoadizRozier/tags/bulkDelete.html.twig', $assignation);
    }

    #[Route(
        path: '/rz-admin/tags/add',
        name: 'tagsAddPage'
    )]
    public function addAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_TAGS');

        $translation = $this->managerRegistry->getRepository(Translation::class)->findDefault();

        if (null === $translation) {
            throw new ResourceNotFoundException();
        }

        $tag = new Tag();
        $form = $this->createForm(TagType::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $tag = $this->createTag($tag, $translation);

            $msg = $this->translator->trans('tag.%name%.created', ['%name%' => $tag->getTagName()]);
            $this->logTrail->publishConfirmMessage($request, $msg, $tag);

            return $this->redirectToRoute('tagsHomePage');
        }

        return $this->render('@RoadizRozier/tags/add.html.twig', [
            'form' => $form->createView(),
            'tag' => $tag,
            'translation' => $translation,
        ]);
    }

    #[Route(
        path: '/rz-admin/tags/edit/{tagId}/settings',
        name: 'tagsSettingsPage',
        requirements: ['tagId' => '\d+'],
    )]
    public function editSettingsAction(
        Request $request,
        #[MapEntity(
            expr: 'repository.find(tagId)',
            evictCache: true,
        )]
        Tag $tag,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_TAGS');

        $translation = $this->managerRegistry->getRepository(Translation::class)->findDefault();

        $form = $this->createForm(TagType::class, $tag, [
            'tagName' => $tag->getTagName(),
        ]);

        $form->handleRequest($request);
        $isJsonRequest =
            $request->isXmlHttpRequest()
            || \in_array('application/json', $request->getAcceptableContentTypes())
        ;

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->managerRegistry->getManager()->flush();
                /*
                 * Dispatch event
                 */
                $this->eventDispatcher->dispatch(new TagUpdatedEvent($tag));

                $msg = $this->translator->trans('tag.%name%.updated', ['%name%' => $tag->getTagName()]);
                $this->logTrail->publishConfirmMessage($request, $msg, $tag);

                /*
                 * Force redirect to avoid resending form when refreshing page
                 */
                return $this->redirectToRoute(
                    'tagsSettingsPage',
                    ['tagId' => $tag->getId()]
                );
            }
            /*
             * Handle errors when Ajax POST requests
             */
            if ($isJsonRequest) {
                $errors = $this->formErrorSerializer->getErrorsAsArray($form);

                return new JsonResponse([
                    'status' => 'fail',
                    'errors' => $errors,
                    'message' => $this->translator->trans('form_has_errors.check_you_fields'),
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        return $this->render('@RoadizRozier/tags/settings.html.twig', [
            'tag' => $tag,
            'form' => $form->createView(),
            'translation' => $translation,
        ]);
    }

    #[Route(
        path: '/rz-admin/tags/tree/{tagId}',
        name: 'tagsTreePage',
        requirements: ['tagId' => '\d+'],
        defaults: ['translationId' => null],
    )]
    public function treeAction(
        Request $request,
        #[MapEntity(
            expr: 'repository.find(tagId)',
            evictCache: true,
        )]
        Tag $tag,
        #[MapEntity(
            expr: 'translationId ? repository.find(translationId) : repository.findDefault()',
        )]
        Translation $translation,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_TAGS');

        $widget = $this->treeWidgetFactory->createTagTree($tag, $translation);

        return $this->render('@RoadizRozier/tags/tree.html.twig', [
            'tag' => $tag,
            'translation' => $translation,
            'specificTagTree' => $widget,
        ]);
    }

    /**
     * Return a deletion form for requested tag.
     */
    #[Route(
        path: '/rz-admin/tags/delete/{tagId}',
        name: 'tagsDeletePage',
        requirements: ['tagId' => '\d+'],
    )]
    public function deleteAction(
        Request $request,
        #[MapEntity(
            expr: 'repository.find(tagId)',
            evictCache: true,
        )]
        Tag $tag,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_TAGS_DELETE');

        if ($tag->isLocked()) {
            throw new ResourceNotFoundException();
        }

        $form = $this->buildDeleteForm($tag);
        $form->handleRequest($request);

        if (
            $form->isSubmitted()
            && $form->isValid()
            && $form->getData()['tagId'] == $tag->getId()
        ) {
            $this->eventDispatcher->dispatch(new TagDeletedEvent($tag));

            $this->managerRegistry->getManager()->remove($tag);
            $this->managerRegistry->getManager()->flush();

            $msg = $this->translator->trans('tag.%name%.deleted', [
                '%name%' => $tag->getTranslatedTags()->first() ?
                    $tag->getTranslatedTags()->first()->getName() :
                    $tag->getTagName(),
            ]);
            $this->logTrail->publishConfirmMessage($request, $msg, $tag);

            return $this->redirectToRoute('tagsHomePage');
        }

        return $this->render('@RoadizRozier/tags/delete.html.twig', [
            'tag' => $tag,
            'form' => $form->createView(),
        ]);
    }

    #[Route(
        path: '/rz-admin/tags/add-child/{tagId}',
        name: 'tagsAddChildPage',
        requirements: ['tagId' => '\d+'],
        defaults: ['translationId' => null],
    )]
    public function addChildAction(
        Request $request,
        #[MapEntity(
            expr: 'repository.find(tagId)',
            evictCache: true,
        )]
        Tag $parentTag,
        #[MapEntity(
            expr: 'translationId ? repository.find(translationId) : repository.findDefault()',
        )]
        Translation $translation,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_TAGS');

        $tag = new Tag();
        $tag->setParent($parentTag);

        $form = $this->createForm(TagType::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $tag = $this->createTag($tag, $translation, $parentTag);

                $msg = $this->translator->trans('child.tag.%name%.created', ['%name%' => $tag->getTagName()]);
                $this->logTrail->publishConfirmMessage($request, $msg, $tag);

                return $this->redirectToRoute(
                    'tagsEditPage',
                    ['tagId' => $tag->getId()]
                );
            } catch (EntityAlreadyExistsException $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        return $this->render('@RoadizRozier/tags/add.html.twig', [
            'form' => $form->createView(),
            'tag' => $tag,
            'parentTag' => $parentTag,
            'translation' => $translation,
        ]);
    }

    #[Route(
        path: '/rz-admin/tags/edit/{tagId}/nodes',
        name: 'tagsEditNodesPage',
        requirements: ['tagId' => '\d+'],
    )]
    public function editNodesAction(
        Request $request,
        #[MapEntity(
            expr: 'repository.find(tagId)',
            evictCache: true,
        )]
        Tag $tag,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_TAGS');

        $translation = $this->managerRegistry->getRepository(Translation::class)->findDefault();

        /*
         * Manage get request to filter list
         */
        $listManager = $this->entityListManagerFactory->createAdminEntityListManager(
            Node::class,
            [
                'tags' => $tag,
            ]
        );
        $listManager->setDisplayingNotPublishedNodes(true);
        $listManager->handle();

        return $this->render('@RoadizRozier/tags/nodes.html.twig', [
            'tag' => $tag,
            'filters' => $listManager->getAssignation(),
            'nodes' => $listManager->getEntities(),
            'translation' => $translation,
        ]);
    }

    private function createTag(Tag $tag, TranslationInterface $translation, ?Tag $parentTag = null): Tag
    {
        /*
         * Get latest position to add tags after.
         */
        $latestPosition = $this->managerRegistry
            ->getRepository(Tag::class)
            ->findLatestPositionInParent($parentTag);
        $tag->setPosition($latestPosition + 1);

        $this->managerRegistry->getManager()->persist($tag);
        $this->managerRegistry->getManager()->flush();

        $translatedTag = new TagTranslation($tag, $translation);
        $this->managerRegistry->getManager()->persist($translatedTag);
        $this->managerRegistry->getManager()->flush();

        $this->eventDispatcher->dispatch(new TagCreatedEvent($tag));

        return $tag;
    }

    private function buildDeleteForm(Tag $tag): FormInterface
    {
        $builder = $this->createFormBuilder()
            ->add('tagId', HiddenType::class, [
                'data' => $tag->getId(),
                'constraints' => [
                    new NotNull(),
                    new NotBlank(),
                ],
            ]);

        return $builder->getForm();
    }

    private function buildBulkDeleteForm(
        ?string $referer = null,
        array $tagsIds = [],
    ): FormInterface {
        $builder = $this->formFactory
            ->createNamedBuilder('deleteForm')
            ->add('tagsIds', HiddenType::class, [
                'data' => \json_encode($tagsIds, flags: JSON_THROW_ON_ERROR),
                'attr' => ['class' => 'bulk-form-value'],
                'constraints' => [
                    new NotNull(),
                    new NotBlank(),
                ],
            ]);

        if (null !== $referer && (new UnicodeString($referer))->startsWith('/')) {
            $builder->add('referer', HiddenType::class, [
                'data' => $referer,
            ]);
        }

        return $builder->getForm();
    }

    private function bulkDeleteTags(array $data): string
    {
        if (empty($data['tagsIds'])) {
            return $this->translator->trans('wrong.request');
        }

        $tagsIds = trim((string) $data['tagsIds']);
        $tagsIds = \json_decode($tagsIds, true, flags: JSON_THROW_ON_ERROR);
        array_filter($tagsIds);

        $tags = $this->managerRegistry->getRepository(Tag::class)
            ->findBy([
                'id' => $tagsIds,
                // Removed locked tags from bulk deletion
                'locked' => false,
            ]);

        foreach ($tags as $tag) {
            /** @var TagHandler $handler */
            $handler = $this->handlerFactory->getHandler($tag);
            $handler->removeWithChildrenAndAssociations();
        }

        $this->managerRegistry->getManager()->flush();

        return $this->translator->trans('tags.bulk.deleted');
    }

    #[\Override]
    protected function onPostUpdate(PersistableInterface $entity, Request $request): void
    {
        if (!$entity instanceof TagTranslation) {
            return;
        }

        $this->managerRegistry->getManager()->flush();
        $this->eventDispatcher->dispatch(
            new TagUpdatedEvent($entity->getTag())
        );

        $msg = $this->translator->trans('tag.%name%.updated', [
            '%name%' => $entity->getName(),
        ]);
        $this->logTrail->publishConfirmMessage($request, $msg, $entity);
    }

    #[\Override]
    protected function getPostUpdateRedirection(PersistableInterface $entity): ?Response
    {
        if (!$entity instanceof TagTranslation) {
            return null;
        }

        $translation = $entity->getTranslation();

        return $this->redirectToRoute(
            'tagsEditTranslatedPage',
            [
                'tagId' => $entity->getTag()->getId(),
                'translationId' => $translation->getId(),
            ]
        );
    }
}
