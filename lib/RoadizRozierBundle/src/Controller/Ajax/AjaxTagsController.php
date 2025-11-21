<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Ajax;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\Handlers\HandlerFactoryInterface;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\EntityHandler\TagHandler;
use RZ\Roadiz\CoreBundle\Event\Tag\TagUpdatedEvent;
use RZ\Roadiz\CoreBundle\Explorer\ExplorerItemFactoryInterface;
use RZ\Roadiz\CoreBundle\ListManager\EntityListManagerFactoryInterface;
use RZ\Roadiz\CoreBundle\Repository\TagRepository;
use RZ\Roadiz\RozierBundle\Model\PositionDto;
use RZ\Roadiz\RozierBundle\Model\TagCreationDto;
use RZ\Roadiz\Utils\StringHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AjaxTagsController extends AbstractAjaxExplorerController
{
    use UpdatePositionTrait;

    public function __construct(
        private readonly HandlerFactoryInterface $handlerFactory,
        private readonly TagRepository $tagRepository,
        ExplorerItemFactoryInterface $explorerItemFactory,
        EventDispatcherInterface $eventDispatcher,
        EntityListManagerFactoryInterface $entityListManagerFactory,
        ManagerRegistry $managerRegistry,
        SerializerInterface $serializer,
        TranslatorInterface $translator,
    ) {
        parent::__construct($explorerItemFactory, $eventDispatcher, $entityListManagerFactory, $managerRegistry, $serializer, $translator);
    }

    protected function getRepository(): TagRepository
    {
        return $this->tagRepository;
    }

    #[Route(
        path: '/rz-admin/ajax/tag/explore',
        name: 'tagsAjaxExplorer',
        methods: ['GET'],
        format: 'json'
    )]
    public function indexAction(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_TAGS');
        $onlyParents = false;

        if (
            $request->query->has('onlyParents')
            && $request->query->get('onlyParents')
        ) {
            $onlyParents = true;
        }

        if ($onlyParents) {
            $tags = $this->getRepository()->findByParentWithChildrenAndDefaultTranslation();
        } else {
            $tags = $this->getRepository()->findByParentWithDefaultTranslation();
        }

        return $this->createSerializedResponse([
            'status' => 'confirm',
            'statusCode' => 200,
            'tags' => $this->recurseTags($tags, $onlyParents),
        ]);
    }

    /**
     * Get a Tag list from an array of node id.
     */
    #[Route(
        path: '/rz-admin/ajax/tag/explore/array',
        name: 'tagsAjaxByArray',
        methods: ['GET'],
        format: 'json'
    )]
    public function listArrayAction(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_TAGS');

        if (!$request->query->has('ids')) {
            throw new InvalidParameterException('Ids should be provided within an array');
        }

        $cleanTagIds = array_filter($request->query->filter('ids', [], \FILTER_DEFAULT, [
            'flags' => \FILTER_FORCE_ARRAY,
        ]));
        $normalizedTags = [];

        if (count($cleanTagIds)) {
            $tags = $this->getRepository()->findBy([
                'id' => $cleanTagIds,
                'translation' => $this->managerRegistry->getRepository(Translation::class)->findDefault(),
            ]);

            // Sort array by ids given in request
            $tags = $this->sortIsh($tags, $cleanTagIds);
            $normalizedTags = $this->normalizeTags($tags);
        }

        return $this->createSerializedResponse([
            'status' => 'confirm',
            'statusCode' => 200,
            'tags' => $normalizedTags,
        ]);
    }

    /**
     * @return Response JSON response
     */
    #[Route(
        path: '/rz-admin/ajax/tag/explore/list',
        name: 'tagsAjaxExplorerList',
        methods: ['GET'],
        format: 'json'
    )]
    public function explorerListAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_TAGS');

        $arrayFilter = [
            'translation' => $this->managerRegistry->getRepository(Translation::class)->findDefault(),
        ];
        $defaultOrder = [
            'createdAt' => 'DESC',
        ];

        if ($request->get('tagId') > 0) {
            $parentTag = $this->tagRepository->find($request->get('tagId'));

            $arrayFilter['parent'] = $parentTag;
        }

        if ($request->query->has('onlyParents')) {
            $arrayFilter['children'] = ['NOT NULL'];
        }

        /*
         * Manage get request to filter list
         */
        $listManager = $this->createEntityListManager(
            Tag::class,
            $arrayFilter,
            $defaultOrder
        );
        $listManager->setDisplayingNotPublishedNodes(true);
        $listManager->setItemPerPage(30);
        $listManager->handle();

        $tags = $listManager->getEntities();

        return $this->createSerializedResponse([
            'status' => 'confirm',
            'statusCode' => 200,
            'tags' => $this->normalizeTags($tags),
            'filters' => $listManager->getAssignation(),
        ]);
    }

    /**
     * @param iterable<Tag>|null $tags
     *
     * @return array<int, array>
     */
    protected function normalizeTags(?iterable $tags): array
    {
        if (null === $tags) {
            return [];
        }
        $tagsArray = [];

        foreach ($tags as $tag) {
            $tagModel = $this->explorerItemFactory->createForEntity($tag);
            $tagsArray[] = $tagModel->toArray();
        }

        return $tagsArray;
    }

    /**
     * @param Tag[]|null $tags
     */
    protected function recurseTags(?array $tags = null, bool $onlyParents = false): array
    {
        if (null === $tags) {
            return [];
        }

        $tagsArray = [];
        foreach ($tags as $tag) {
            if ($onlyParents) {
                $children = $this->getRepository()->findByParentWithChildrenAndDefaultTranslation($tag);
            } else {
                $children = $this->getRepository()->findByParentWithDefaultTranslation($tag);
            }

            $tagsArray[] = [
                'id' => $tag->getId(),
                'name' => $tag->getTranslatedTags()->first() ? $tag->getTranslatedTags()->first()->getName() : $tag->getTagName(),
                'children' => $this->recurseTags($children, $onlyParents),
            ];
        }

        return $tagsArray;
    }

    #[Route(
        path: '/rz-admin/ajax/tag/position',
        name: 'tagPositionAjax',
        methods: ['POST'],
        format: 'json'
    )]
    public function editPositionAction(
        #[MapRequestPayload]
        PositionDto $tagPositionDto,
    ): JsonResponse {
        $this->validateCsrfToken($tagPositionDto->csrfToken);
        $this->denyAccessUnlessGranted('ROLE_ACCESS_TAGS');

        $tag = $this->tagRepository->find($tagPositionDto->id);

        if (null === $tag) {
            throw $this->createNotFoundException('Tag '.$tagPositionDto->id.' does not exists');
        }

        $this->updatePositionAndParent($tagPositionDto, $tag, $this->tagRepository);

        // Apply position update before cleaning
        $this->managerRegistry->getManager()->flush();

        /** @var TagHandler $tagHandler */
        $tagHandler = $this->handlerFactory->getHandler($tag);
        $tagHandler->cleanPositions();

        $this->managerRegistry->getManager()->flush();

        $this->eventDispatcher->dispatch(new TagUpdatedEvent($tag));

        return new JsonResponse(
            [
                'statusCode' => '200',
                'status' => 'success',
                'responseText' => ('Tag '.$tagPositionDto->id.' edited '),
            ],
            Response::HTTP_PARTIAL_CONTENT
        );
    }

    #[Route(
        path: '/rz-admin/ajax/tag/search',
        name: 'tagAjaxSearch',
        methods: ['GET'],
        format: 'json'
    )]
    public function searchAction(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_TAGS');

        if (empty($request->get('search'))) {
            throw new BadRequestHttpException('Search is empty.');
        }

        $responseArray = [];
        $pattern = strip_tags((string) $request->get('search'));
        $tags = $this->getRepository()->searchBy($pattern, [], [], 10);

        if (0 === count($tags)) {
            /*
             * Try again using tag slug
             */
            $pattern = StringHandler::slugify($pattern);
            $tags = $this->getRepository()->searchBy($pattern, [], [], 10);
        }

        if (0 === count($tags)) {
            throw $this->createNotFoundException('No tags found.');
        }

        /** @var Tag $tag */
        foreach ($tags as $tag) {
            $responseArray[] = $tag->getFullPath();
        }

        return $this->createSerializedResponse(
            $responseArray
        );
    }

    /**
     * Create a new Tag.
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    #[Route(
        path: '/rz-admin/ajax/tag/create',
        name: 'tagsAjaxCreate',
        methods: ['POST'],
        format: 'json'
    )]
    public function createAction(
        #[MapRequestPayload]
        TagCreationDto $tagCreationDto,
    ): JsonResponse {
        $this->validateCsrfToken($tagCreationDto->csrfToken);
        $this->denyAccessUnlessGranted('ROLE_ACCESS_TAGS');

        /** @var Tag $tag */
        $tag = $this->getRepository()->findOrCreateByPath($tagCreationDto->tagName);
        $tagModel = $this->explorerItemFactory->createForEntity($tag);

        return new JsonResponse(
            [
                'tag' => $tagModel->toArray(),
            ],
            Response::HTTP_CREATED
        );
    }
}
