<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers;

use Doctrine\Persistence\ObjectRepository;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\ListManager\SessionListFilters;
use Symfony\Component\Form\Exception\InvalidConfigurationException;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\String\UnicodeString;
use Symfony\Contracts\EventDispatcher\Event;
use Themes\Rozier\RozierApp;

abstract class AbstractAdminController extends RozierApp
{
    public const ITEM_PER_PAGE = 20;

    public function __construct(
        protected readonly SerializerInterface $serializer,
        protected readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    protected function getThemeDirectory(): string
    {
        return RozierApp::getThemeDir();
    }

    protected function getTemplateNamespace(): string
    {
        return '@RoadizRozier';
    }

    protected function additionalAssignation(Request $request): void
    {
        $this->assignation['controller_namespace'] = $this->getNamespace();
    }

    protected function prepareWorkingItem(PersistableInterface $item): void
    {
        // Add or modify current working item.
    }

    protected function getRepository(): ObjectRepository
    {
        return $this->em()->getRepository($this->getEntityClass());
    }

    protected function getRequiredDeletionRole(): string
    {
        return $this->getRequiredRole();
    }

    protected function getRequiredListingRole(): string
    {
        return $this->getRequiredRole();
    }

    protected function getRequiredCreationRole(): string
    {
        return $this->getRequiredRole();
    }

    protected function getRequiredEditionRole(): string
    {
        return $this->getRequiredRole();
    }

    protected function getRequiredExportRole(): string
    {
        return $this->getRequiredRole();
    }

    /**
     * @throws \Twig\Error\RuntimeError
     */
    public function defaultAction(Request $request): ?Response
    {
        $this->denyAccessUnlessGranted($this->getRequiredListingRole());
        $this->additionalAssignation($request);

        $elm = $this->createEntityListManager(
            $this->getEntityClass(),
            $this->getDefaultCriteria($request),
            $this->getDefaultOrder($request)
        );
        $elm->setDisplayingNotPublishedNodes(true);
        /*
         * Stored item per pages in session
         */
        $sessionListFilter = new SessionListFilters($this->getNamespace().'_item_per_page');
        $sessionListFilter->handleItemPerPage($request, $elm);
        $elm->handle();

        $this->assignation['items'] = $elm->getEntities();
        $this->assignation['filters'] = $elm->getAssignation();

        return $this->render(
            $this->getTemplateFolder().'/list.html.twig',
            $this->assignation,
            null,
            $this->getTemplateNamespace()
        );
    }

    /**
     * @throws \Twig\Error\RuntimeError
     */
    public function addAction(Request $request): ?Response
    {
        $this->denyAccessUnlessGranted($this->getRequiredCreationRole());
        $this->additionalAssignation($request);

        $item = $this->createEmptyItem($request);
        $this->prepareWorkingItem($item);
        $form = $this->createForm($this->getFormTypeFromRequest($request), $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /*
             * Events are dispatched before entity manager is flushed
             * to be able to throw exceptions before it is persisted.
             */
            $event = $this->createCreateEvent($item);
            $this->dispatchSingleOrMultipleEvent($event);

            $this->em()->persist($item);
            $this->em()->flush();

            $postEvent = $this->createPostCreateEvent($item);
            $this->dispatchSingleOrMultipleEvent($postEvent);

            $msg = $this->getTranslator()->trans(
                '%namespace%.%item%.was_created',
                [
                    '%item%' => $this->getEntityName($item),
                    '%namespace%' => $this->getTranslator()->trans($this->getNamespace()),
                ]
            );
            $this->publishConfirmMessage($request, $msg, $item);

            return $this->getPostSubmitResponse($item, true, $request);
        }

        $this->assignation['form'] = $form->createView();
        $this->assignation['item'] = $item;

        return $this->render(
            $this->getTemplateFolder().'/add.html.twig',
            $this->assignation,
            null,
            $this->getTemplateNamespace()
        );
    }

    /**
     * @param int|string $id Numeric ID or UUID
     *
     * @throws \Twig\Error\RuntimeError
     */
    public function editAction(Request $request, $id): ?Response
    {
        $this->denyAccessUnlessGranted($this->getRequiredEditionRole());
        $this->additionalAssignation($request);

        /** @var mixed|object|null $item */
        $item = $this->em()->find($this->getEntityClass(), $id);
        if (!($item instanceof PersistableInterface)) {
            throw $this->createNotFoundException();
        }

        $this->prepareWorkingItem($item);
        $this->denyAccessUnlessItemGranted($item);

        $form = $this->createForm($this->getFormTypeFromRequest($request), $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /*
             * Events are dispatched before entity manager is flushed
             * to be able to throw exceptions before it is persisted.
             */
            $event = $this->createUpdateEvent($item);
            $this->dispatchSingleOrMultipleEvent($event);
            $this->em()->flush();

            /*
             * Event that requires that EM is flushed
             */
            $postEvent = $this->createPostUpdateEvent($item);
            $this->dispatchSingleOrMultipleEvent($postEvent);

            $msg = $this->getTranslator()->trans(
                '%namespace%.%item%.was_updated',
                [
                    '%item%' => $this->getEntityName($item),
                    '%namespace%' => $this->getTranslator()->trans($this->getNamespace()),
                ]
            );
            $this->publishConfirmMessage($request, $msg, $item);

            return $this->getPostSubmitResponse($item, false, $request);
        }

        $this->assignation['form'] = $form->createView();
        $this->assignation['item'] = $item;

        return $this->render(
            $this->getTemplateFolder().'/edit.html.twig',
            $this->assignation,
            null,
            $this->getTemplateNamespace()
        );
    }

    public function exportAction(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted($this->getRequiredExportRole());
        $this->additionalAssignation($request);

        $items = $this->getRepository()->findAll();

        return new JsonResponse(
            $this->serializer->serialize(
                $items,
                'json',
                SerializationContext::create()->setGroups([$this->getNamespace()])
            ),
            Response::HTTP_OK,
            [
                'Content-Disposition' => sprintf(
                    'attachment; filename="%s_%s.json"',
                    $this->getNamespace(),
                    (new \DateTime())->format('YmdHi')
                ),
            ],
            true
        );
    }

    /**
     * @param int|string $id Numeric ID or UUID
     *
     * @throws \Twig\Error\RuntimeError
     */
    public function deleteAction(Request $request, $id): ?Response
    {
        $this->denyAccessUnlessGranted($this->getRequiredDeletionRole());
        $this->additionalAssignation($request);

        /** @var mixed|object|null $item */
        $item = $this->em()->find($this->getEntityClass(), $id);

        if (!($item instanceof PersistableInterface)) {
            throw $this->createNotFoundException();
        }

        $this->prepareWorkingItem($item);
        $this->denyAccessUnlessItemGranted($item);

        $form = $this->createForm(FormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /*
             * Events are dispatched before entity manager is flushed
             * to be able to throw exceptions before it is persisted.
             */
            $event = $this->createDeleteEvent($item);
            $this->dispatchSingleOrMultipleEvent($event);
            $this->em()->remove($item);
            $this->em()->flush();

            $postEvent = $this->createPostDeleteEvent($item);
            $this->dispatchSingleOrMultipleEvent($postEvent);

            $msg = $this->getTranslator()->trans(
                '%namespace%.%item%.was_deleted',
                [
                    '%item%' => $this->getEntityName($item),
                    '%namespace%' => $this->getTranslator()->trans($this->getNamespace()),
                ]
            );
            $this->publishConfirmMessage($request, $msg, $item);

            return $this->getPostDeleteResponse($item);
        }

        $this->assignation['form'] = $form->createView();
        $this->assignation['item'] = $item;

        return $this->render(
            $this->getTemplateFolder().'/delete.html.twig',
            $this->assignation,
            null,
            $this->getTemplateNamespace()
        );
    }

    abstract protected function supports(PersistableInterface $item): bool;

    /**
     * @return string namespace is used for composing messages and translations
     */
    abstract protected function getNamespace(): string;

    abstract protected function createEmptyItem(Request $request): PersistableInterface;

    abstract protected function getTemplateFolder(): string;

    abstract protected function getRequiredRole(): string;

    /**
     * @return class-string<PersistableInterface>
     */
    abstract protected function getEntityClass(): string;

    /**
     * @return class-string
     */
    abstract protected function getFormType(): string;

    /**
     * @return class-string
     */
    protected function getFormTypeFromRequest(Request $request): string
    {
        /*
         * Routing can define defaults._type to change edition Form dynamically.
         */
        if (null !== $type = $request->attributes->get('_type')) {
            if (!class_exists($type)) {
                throw new InvalidConfigurationException(\sprintf('Route uses non-existent %s form type class.', $type));
            }

            return (string) $type;
        }

        // Falls back on child-class implemented form type.
        return $this->getFormType();
    }

    protected function getDefaultCriteria(Request $request): array
    {
        return [];
    }

    protected function getDefaultOrder(Request $request): array
    {
        return [];
    }

    abstract protected function getDefaultRouteName(): string;

    /**
     * @return array<string, string|int|null>
     */
    protected function getDefaultRouteParameters(): array
    {
        return [];
    }

    abstract protected function getEditRouteName(): string;

    protected function getPostSubmitResponse(
        PersistableInterface $item,
        bool $forceDefaultEditRoute = false,
        ?Request $request = null,
    ): Response {
        if (null === $request) {
            // Redirect to default route if no request provided
            return $this->redirect($this->urlGenerator->generate(
                $this->getEditRouteName(),
                $this->getEditRouteParameters($item)
            ));
        }

        $route = $request->attributes->get('_route');
        $referrer = $request->query->get('referer');

        /*
         * Force redirect to avoid resending form when refreshing page
         */
        if (
            \is_string($referrer)
            && '' !== $referrer
            && (new UnicodeString($referrer))->trim()->startsWith('/')
        ) {
            return $this->redirect($referrer);
        }

        /*
         * Try to redirect to same route as defined in Request attribute
         */
        if (
            false === $forceDefaultEditRoute
            && \is_string($route)
            && '' !== $route
        ) {
            return $this->redirect($this->urlGenerator->generate(
                $route,
                $this->getEditRouteParameters($item)
            ));
        }

        return $this->redirect($this->urlGenerator->generate(
            $this->getEditRouteName(),
            $this->getEditRouteParameters($item)
        ));
    }

    protected function getEditRouteParameters(PersistableInterface $item): array
    {
        return [
            'id' => $item->getId(),
        ];
    }

    protected function getPostDeleteResponse(PersistableInterface $item): Response
    {
        return $this->redirect($this->urlGenerator->generate(
            $this->getDefaultRouteName(),
            $this->getDefaultRouteParameters()
        ));
    }

    /**
     * @template T of object|Event
     *
     * @param T|iterable<T>|array<int, T>|null $event
     *
     * @return T|iterable<T>|array<int, T>|null
     */
    protected function dispatchSingleOrMultipleEvent(mixed $event): object|array|null
    {
        if (null === $event) {
            return null;
        }
        if ($event instanceof Event) {
            // @phpstan-ignore-next-line
            return $this->dispatchEvent($event);
        }
        if (\is_iterable($event)) {
            $events = [];
            /** @var T|null $singleEvent */
            foreach ($event as $singleEvent) {
                $returningEvent = $this->dispatchSingleOrMultipleEvent($singleEvent);
                if ($returningEvent instanceof Event) {
                    $events[] = $returningEvent;
                }
            }

            return $events;
        }
        throw new \InvalidArgumentException('Event must be null, Event or array of Event');
    }

    /**
     * @return Event|Event[]|null
     */
    protected function createCreateEvent(PersistableInterface $item)
    {
        return null;
    }

    /**
     * @return Event|Event[]|null
     */
    protected function createPostCreateEvent(PersistableInterface $item)
    {
        return null;
    }

    /**
     * @return Event|Event[]|null
     */
    protected function createUpdateEvent(PersistableInterface $item)
    {
        return null;
    }

    /**
     * @return Event|Event[]|null
     */
    protected function createPostUpdateEvent(PersistableInterface $item)
    {
        return null;
    }

    /**
     * @return Event|Event[]|null
     */
    protected function createDeleteEvent(PersistableInterface $item)
    {
        return null;
    }

    /**
     * @return Event|Event[]|null
     */
    protected function createPostDeleteEvent(PersistableInterface $item)
    {
        return null;
    }

    abstract protected function getEntityName(PersistableInterface $item): string;

    protected function denyAccessUnlessItemGranted(PersistableInterface $item): void
    {
        // Do nothing
    }
}
