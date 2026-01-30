<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller;

use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Entity\Redirection;
use RZ\Roadiz\CoreBundle\Event\Redirection\PostCreatedRedirectionEvent;
use RZ\Roadiz\CoreBundle\Event\Redirection\PostDeletedRedirectionEvent;
use RZ\Roadiz\CoreBundle\Event\Redirection\PostUpdatedRedirectionEvent;
use RZ\Roadiz\CoreBundle\Event\Redirection\RedirectionEvent;
use RZ\Roadiz\RozierBundle\Form\RedirectionType;
use Symfony\Component\HttpFoundation\Request;

final class RedirectionController extends AbstractAdminWithBulkController
{
    #[\Override]
    protected function supports(PersistableInterface $item): bool
    {
        return $item instanceof Redirection;
    }

    #[\Override]
    protected function getNamespace(): string
    {
        return 'redirection';
    }

    #[\Override]
    protected function createEmptyItem(Request $request): PersistableInterface
    {
        return new Redirection();
    }

    #[\Override]
    protected function getTemplateFolder(): string
    {
        return '@RoadizRozier/redirections';
    }

    #[\Override]
    protected function getRequiredRole(): string
    {
        return 'ROLE_ACCESS_REDIRECTIONS';
    }

    #[\Override]
    protected function getEntityClass(): string
    {
        return Redirection::class;
    }

    #[\Override]
    protected function getFormType(): string
    {
        return RedirectionType::class;
    }

    #[\Override]
    protected function getDefaultRouteName(): string
    {
        return 'redirectionsHomePage';
    }

    #[\Override]
    protected function getEditRouteName(): string
    {
        return 'redirectionsEditPage';
    }

    #[\Override]
    protected function getEntityName(PersistableInterface $item): string
    {
        if ($item instanceof Redirection) {
            return (string) $item->getQuery();
        }
        throw new \InvalidArgumentException('Item should be instance of '.$this->getEntityClass());
    }

    #[\Override]
    protected function getDefaultOrder(Request $request): array
    {
        return ['query' => 'ASC'];
    }

    #[\Override]
    protected function createPostCreateEvent(PersistableInterface $item): RedirectionEvent
    {
        if (!$item instanceof Redirection) {
            throw new \InvalidArgumentException('Item should be instance of '.Redirection::class);
        }

        return new PostCreatedRedirectionEvent($item);
    }

    #[\Override]
    protected function createPostUpdateEvent(PersistableInterface $item): RedirectionEvent
    {
        if (!$item instanceof Redirection) {
            throw new \InvalidArgumentException('Item should be instance of '.Redirection::class);
        }

        return new PostUpdatedRedirectionEvent($item);
    }

    #[\Override]
    protected function createDeleteEvent(PersistableInterface $item): RedirectionEvent
    {
        if (!$item instanceof Redirection) {
            throw new \InvalidArgumentException('Item should be instance of '.Redirection::class);
        }

        return new PostDeletedRedirectionEvent($item);
    }

    #[\Override]
    protected function getBulkDeleteRouteName(): string
    {
        return 'redirectionsBulkDeletePage';
    }
}
