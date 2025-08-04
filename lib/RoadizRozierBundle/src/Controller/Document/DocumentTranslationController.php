<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Document;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\DocumentTranslation;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Event\Document\DocumentTranslationUpdatedEvent;
use RZ\Roadiz\CoreBundle\Security\LogTrail;
use RZ\Roadiz\RozierBundle\Controller\VersionedControllerTrait;
use RZ\Roadiz\RozierBundle\Form\DocumentTranslationType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Error\RuntimeError;

#[AsController]
final class DocumentTranslationController extends AbstractController
{
    use VersionedControllerTrait;

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly ManagerRegistry $managerRegistry,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly FormFactoryInterface $formFactory,
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

    /**
     * @throws RuntimeError
     */
    public function editAction(Request $request, int $documentId, ?int $translationId = null): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCUMENTS');

        /** @var Document|null $document */
        $document = $this->managerRegistry
            ->getRepository(Document::class)
            ->find($documentId);

        if (null === $document) {
            throw new ResourceNotFoundException();
        }

        if (null === $translationId) {
            $translation = $this->managerRegistry->getRepository(Translation::class)->findDefault();
            if ($translation instanceof PersistableInterface) {
                $translationId = $translation->getId();
            }
        } else {
            $translation = $this->managerRegistry->getRepository(Translation::class)->find($translationId);
        }

        $documentTr = $this->managerRegistry
                           ->getRepository(DocumentTranslation::class)
                           ->findOneBy(['document' => $documentId, 'translation' => $translationId]);

        if (null === $documentTr && null !== $translation) {
            $documentTr = $this->createDocumentTranslation($document, $translation);
        }
        if (null === $documentTr) {
            throw new ResourceNotFoundException();
        }

        $assignation = [];

        if ($this->isGranted('ROLE_ACCESS_VERSIONS')) {
            if (null !== $response = $this->handleVersions($request, $documentTr, $assignation)) {
                return $response;
            }
        }

        $form = $this->createForm(DocumentTranslationType::class, $documentTr, [
            'referer' => $request->get('referer'),
            'disabled' => $this->isReadOnly,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->onPostUpdate($documentTr, $request);

            $routeParams = [
                'documentId' => $document->getId(),
                'translationId' => $translationId,
            ];

            if ($form->get('referer')->getData()) {
                $routeParams = array_merge($routeParams, [
                    'referer' => $form->get('referer')->getData(),
                ]);
            }

            return $this->redirectToRoute(
                'documentsMetaPage',
                $routeParams
            );
        }

        return $this->render('@RoadizRozier/document-translations/edit.html.twig', [
            ...$assignation,
            'document' => $document,
            'translation' => $translation,
            'documentTr' => $documentTr,
            'available_translations' => $this->managerRegistry->getRepository(Translation::class)->findAll(),
            'readOnly' => $this->isReadOnly,
            'form' => $form->createView(),
        ]);
    }

    protected function createDocumentTranslation(
        Document $document,
        TranslationInterface $translation,
    ): DocumentTranslation {
        $dt = new DocumentTranslation();
        $dt->setDocument($document);
        $dt->setTranslation($translation);

        $this->managerRegistry->getManagerForClass(DocumentTranslation::class)->persist($dt);

        return $dt;
    }

    #[\Override]
    protected function onPostUpdate(PersistableInterface $entity, Request $request): void
    {
        if (!$entity instanceof DocumentTranslation) {
            return;
        }

        $this->eventDispatcher->dispatch(
            new DocumentTranslationUpdatedEvent($entity->getDocument(), $entity)
        );
        $this->managerRegistry->getManagerForClass(DocumentTranslation::class)->flush();
        $msg = $this->translator->trans('document.translation.%name%.updated', [
            '%name%' => (string) $entity->getDocument(),
        ]);
        $this->logTrail->publishConfirmMessage($request, $msg, $entity);
    }

    #[\Override]
    protected function getPostUpdateRedirection(PersistableInterface $entity): ?Response
    {
        if (
            $entity instanceof DocumentTranslation
            && $entity->getDocument() instanceof Document
            && $entity->getTranslation() instanceof Translation
        ) {
            $routeParams = [
                'documentId' => $entity->getDocument()->getId(),
                'translationId' => $entity->getTranslation()->getId(),
            ];

            /*
             * Force redirect to avoid resending form when refreshing page
             */
            return $this->redirectToRoute(
                'documentsMetaPage',
                $routeParams
            );
        }

        return null;
    }
}
