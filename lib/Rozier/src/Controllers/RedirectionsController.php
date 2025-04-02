<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers;

use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Entity\Redirection;
use RZ\Roadiz\CoreBundle\Event\Redirection\PostCreatedRedirectionEvent;
use RZ\Roadiz\CoreBundle\Event\Redirection\PostDeletedRedirectionEvent;
use RZ\Roadiz\CoreBundle\Event\Redirection\PostUpdatedRedirectionEvent;
use RZ\Roadiz\CoreBundle\Event\Redirection\RedirectionEvent;
use Symfony\Component\HttpFoundation\Request;
use Themes\Rozier\Forms\RedirectionType;

final class RedirectionsController extends AbstractAdminWithBulkController
{
    protected function supports(PersistableInterface $item): bool
    {
        return $item instanceof Redirection;
    }

    protected function getNamespace(): string
    {
        return 'redirection';
    }

    protected function createEmptyItem(Request $request): PersistableInterface
    {
        return new Redirection();
    }

    protected function getTemplateFolder(): string
    {
        return '@RoadizRozier/redirections';
    }

    protected function getRequiredRole(): string
    {
        return 'ROLE_ACCESS_REDIRECTIONS';
    }

    protected function getEntityClass(): string
    {
        return Redirection::class;
    }

    protected function getFormType(): string
    {
        return RedirectionType::class;
    }

    protected function getDefaultRouteName(): string
    {
        return 'redirectionsHomePage';
    }

    protected function getEditRouteName(): string
    {
        return 'redirectionsEditPage';
    }

    protected function getEntityName(PersistableInterface $item): string
    {
        if ($item instanceof Redirection) {
            return (string) $item->getQuery();
        }
        throw new \InvalidArgumentException('Item should be instance of '.$this->getEntityClass());
    }

    protected function getDefaultOrder(Request $request): array
    {
        return ['query' => 'ASC'];
    }

    protected function createPostCreateEvent(PersistableInterface $item): RedirectionEvent
    {
        if (!($item instanceof Redirection)) {
            throw new \InvalidArgumentException('Item should be instance of '.Redirection::class);
        }

        return new PostCreatedRedirectionEvent($item);
    }

    protected function createPostUpdateEvent(PersistableInterface $item): RedirectionEvent
    {
        if (!($item instanceof Redirection)) {
            throw new \InvalidArgumentException('Item should be instance of '.Redirection::class);
        }

        return new PostUpdatedRedirectionEvent($item);
    }

    protected function createDeleteEvent(PersistableInterface $item): RedirectionEvent
    {
        if (!($item instanceof Redirection)) {
            throw new \InvalidArgumentException('Item should be instance of '.Redirection::class);
        }

        return new PostDeletedRedirectionEvent($item);
    }

    protected function getBulkDeleteRouteName(): ?string
    {
        return 'redirectionsBulkDeletePage';
    }
}
