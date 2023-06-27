<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use Symfony\Component\Form\Exception\InvalidConfigurationException;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\String\UnicodeString;
use Symfony\Contracts\EventDispatcher\Event;
use Themes\Rozier\RozierApp;
use Themes\Rozier\Utils\SessionListFilters;

abstract class AbstractAdminController extends RozierApp
{
    public const ITEM_PER_PAGE = 20;

    protected SerializerInterface $serializer;
    protected UrlGeneratorInterface $urlGenerator;

    /**
     * @param SerializerInterface $serializer
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(SerializerInterface $serializer, UrlGeneratorInterface $urlGenerator)
    {
        $this->serializer = $serializer;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @return string
     */
    protected function getThemeDirectory(): string
    {
        return RozierApp::getThemeDir();
    }

    /**
     * @return string
     */
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

    /**
     * @param Request $request
     * @return Response|null
     * @throws \Twig\Error\RuntimeError
     */
    public function defaultAction(Request $request)
    {
        $this->denyAccessUnlessGranted($this->getRequiredRole());
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
        $sessionListFilter = new SessionListFilters($this->getNamespace() . '_item_per_page');
        $sessionListFilter->handleItemPerPage($request, $elm);
        $elm->handle();

        $this->assignation['items'] = $elm->getEntities();
        $this->assignation['filters'] = $elm->getAssignation();

        return $this->render(
            $this->getTemplateFolder() . '/list.html.twig',
            $this->assignation,
            null,
            $this->getTemplateNamespace()
        );
    }

    /**
     * @param Request $request
     * @return RedirectResponse|Response|null
     * @throws \Twig\Error\RuntimeError
     */
    public function addAction(Request $request)
    {
        $this->denyAccessUnlessGranted($this->getRequiredRole());
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
                    '%namespace%' => $this->getTranslator()->trans($this->getNamespace())
                ]
            );
            $this->publishConfirmMessage($request, $msg);

            return $this->getPostSubmitResponse($item, true, $request);
        }

        $this->assignation['form'] = $form->createView();
        $this->assignation['item'] = $item;

        return $this->render(
            $this->getTemplateFolder() . '/add.html.twig',
            $this->assignation,
            null,
            $this->getTemplateNamespace()
        );
    }

    /**
     * @param Request $request
     * @param int|string $id Numeric ID or UUID
     * @return Response|null
     * @throws \Twig\Error\RuntimeError
     */
    public function editAction(Request $request, $id)
    {
        $this->denyAccessUnlessGranted($this->getRequiredRole());
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
                    '%namespace%' => $this->getTranslator()->trans($this->getNamespace())
                ]
            );
            $this->publishConfirmMessage($request, $msg);

            return $this->getPostSubmitResponse($item, false, $request);
        }

        $this->assignation['form'] = $form->createView();
        $this->assignation['item'] = $item;

        return $this->render(
            $this->getTemplateFolder() . '/edit.html.twig',
            $this->assignation,
            null,
            $this->getTemplateNamespace()
        );
    }

    public function exportAction(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted($this->getRequiredRole());
        $this->additionalAssignation($request);

        $items = $this->em()->getRepository($this->getEntityClass())->findAll();

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
     * @param Request $request
     * @param int|string $id Numeric ID or UUID
     * @return RedirectResponse|Response|null
     * @throws \Twig\Error\RuntimeError
     */
    public function deleteAction(Request $request, $id)
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
                    '%namespace%' => $this->getTranslator()->trans($this->getNamespace())
                ]
            );
            $this->publishConfirmMessage($request, $msg);

            return $this->getPostDeleteResponse($item);
        }

        $this->assignation['form'] = $form->createView();
        $this->assignation['item'] = $item;

        return $this->render(
            $this->getTemplateFolder() . '/delete.html.twig',
            $this->assignation,
            null,
            $this->getTemplateNamespace()
        );
    }

    /**
     * @param PersistableInterface $item
     * @return bool
     */
    abstract protected function supports(PersistableInterface $item): bool;

    /**
     * @return string Namespace is used for composing messages and translations.
     */
    abstract protected function getNamespace(): string;

    /**
     * @param Request $request
     * @return PersistableInterface
     */
    abstract protected function createEmptyItem(Request $request): PersistableInterface;

    /**
     * @return string
     */
    abstract protected function getTemplateFolder(): string;

    /**
     * @return string
     */
    abstract protected function getRequiredRole(): string;

    /**
     * @return string
     */
    protected function getRequiredDeletionRole(): string
    {
        return $this->getRequiredRole();
    }

    /**
     * @return class-string<PersistableInterface>
     */
    abstract protected function getEntityClass(): string;

    /**
     * @return class-string
     */
    abstract protected function getFormType(): string;

    /**
     * @param Request $request
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

    /**
     * @param Request $request
     * @return array
     */
    protected function getDefaultCriteria(Request $request): array
    {
        return [];
    }

    /**
     * @param Request $request
     * @return array
     */
    protected function getDefaultOrder(Request $request): array
    {
        return [];
    }

    /**
     * @return string
     */
    abstract protected function getDefaultRouteName(): string;

    /**
     * @return string
     */
    abstract protected function getEditRouteName(): string;

    /**
     * @param PersistableInterface $item
     * @param bool $forceDefaultEditRoute
     * @param Request|null $request
     * @return Response
     */
    protected function getPostSubmitResponse(
        PersistableInterface $item,
        bool $forceDefaultEditRoute = false,
        ?Request $request = null
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
            \is_string($referrer) &&
            $referrer !== '' &&
            (new UnicodeString($referrer))->trim()->startsWith('/')
        ) {
            return $this->redirect($referrer);
        }

        /*
         * Try to redirect to same route as defined in Request attribute
         */
        if (
            false === $forceDefaultEditRoute &&
            \is_string($route) &&
            $route !== ''
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

    /**
     * @param PersistableInterface $item
     * @return array
     */
    protected function getEditRouteParameters(PersistableInterface $item): array
    {
        return [
            'id' => $item->getId()
        ];
    }

    /**
     * @param PersistableInterface $item
     * @return Response
     */
    protected function getPostDeleteResponse(PersistableInterface $item): Response
    {
        return $this->redirect($this->urlGenerator->generate($this->getDefaultRouteName()));
    }

    /**
     * @template T of object|Event
     * @param T|iterable<T>|array<int, T>|null $event
     * @return T|iterable<T>|array<int, T>|null
     */
    protected function dispatchSingleOrMultipleEvent(mixed $event): mixed
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
     * @param PersistableInterface $item
     * @return Event|Event[]|null
     */
    protected function createCreateEvent(PersistableInterface $item)
    {
        return null;
    }

    /**
     * @param PersistableInterface $item
     * @return Event|Event[]|null
     */
    protected function createPostCreateEvent(PersistableInterface $item)
    {
        return null;
    }

    /**
     * @param PersistableInterface $item
     * @return Event|Event[]|null
     */
    protected function createUpdateEvent(PersistableInterface $item)
    {
        return null;
    }

    /**
     * @param PersistableInterface $item
     * @return Event|Event[]|null
     */
    protected function createPostUpdateEvent(PersistableInterface $item)
    {
        return null;
    }

    /**
     * @param PersistableInterface $item
     * @return Event|Event[]|null
     */
    protected function createDeleteEvent(PersistableInterface $item)
    {
        return null;
    }

    /**
     * @param PersistableInterface $item
     * @return Event|Event[]|null
     */
    protected function createPostDeleteEvent(PersistableInterface $item)
    {
        return null;
    }

    /**
     * @param PersistableInterface $item
     * @return string
     */
    abstract protected function getEntityName(PersistableInterface $item): string;

    /**
     * @param PersistableInterface $item
     */
    protected function denyAccessUnlessItemGranted(PersistableInterface $item): void
    {
        // Do nothing
    }
}
