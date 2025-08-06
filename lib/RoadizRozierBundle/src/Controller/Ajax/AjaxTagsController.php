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
use RZ\Roadiz\Utils\StringHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AjaxTagsController extends AbstractAjaxExplorerController
{
    public function __construct(
        private readonly HandlerFactoryInterface $handlerFactory,
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
        return $this->managerRegistry->getRepository(Tag::class);
    }

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
            $parentTag = $this->managerRegistry->getRepository(Tag::class)->find($request->get('tagId'));

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

    /**
     * Handle AJAX edition requests for Tag
     * such as coming from tag-tree widgets.
     */
    public function editAction(Request $request, int $tagId): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_TAGS');

        $tag = $this->managerRegistry->getRepository(Tag::class)->find($tagId);

        if (null === $tag) {
            throw $this->createNotFoundException('Tag '.$tagId.' does not exists');
        }
        /*
         * Get the right update method against "_action" parameter
         */
        if ('updatePosition' !== $request->get('_action')) {
            throw new BadRequestHttpException('Action does not exist');
        }

        $this->updatePosition($request->request->all(), $tag);

        return new JsonResponse(
            [
                'statusCode' => '200',
                'status' => 'success',
                'responseText' => ('Tag '.$tagId.' edited '),
            ],
            Response::HTTP_PARTIAL_CONTENT
        );
    }

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

    protected function updatePosition(array $parameters, Tag $tag): void
    {
        /*
         * First, we set the new parent
         */
        if (
            !empty($parameters['newParent'])
            && is_numeric($parameters['newParent'])
            && $parameters['newParent'] > 0
        ) {
            $parent = $this->managerRegistry->getRepository(Tag::class)->find((int) $parameters['newParent']);
            if (null !== $parent) {
                $tag->setParent($parent);
            }
        } else {
            $tag->setParent(null);
        }

        /*
         * Then compute new position
         */
        if (
            !empty($parameters['nextTagId'])
            && $parameters['nextTagId'] > 0
        ) {
            $nextTag = $this->managerRegistry->getRepository(Tag::class)->find((int) $parameters['nextTagId']);
            if (null !== $nextTag) {
                $tag->setPosition($nextTag->getPosition() - 0.5);
            }
        } elseif (
            !empty($parameters['prevTagId'])
            && $parameters['prevTagId'] > 0
        ) {
            $prevTag = $this->managerRegistry->getRepository(Tag::class)->find((int) $parameters['prevTagId']);
            if (null !== $prevTag) {
                $tag->setPosition($prevTag->getPosition() + 0.5);
            }
        }
        // Apply position update before cleaning
        $this->managerRegistry->getManager()->flush();

        /** @var TagHandler $tagHandler */
        $tagHandler = $this->handlerFactory->getHandler($tag);
        $tagHandler->cleanPositions();

        $this->managerRegistry->getManager()->flush();

        $this->eventDispatcher->dispatch(new TagUpdatedEvent($tag));
    }

    /**
     * Create a new Tag.
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function createAction(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_TAGS');

        if (!$request->get('tagName')) {
            throw new InvalidParameterException('tagName should be provided to create a new Tag');
        }

        if (Request::METHOD_POST != $request->getMethod()) {
            throw new BadRequestHttpException();
        }

        /** @var Tag $tag */
        $tag = $this->getRepository()->findOrCreateByPath($request->get('tagName'));
        $tagModel = $this->explorerItemFactory->createForEntity($tag);

        return new JsonResponse(
            [
                'tag' => $tagModel->toArray(),
            ],
            Response::HTTP_CREATED
        );
    }
}
