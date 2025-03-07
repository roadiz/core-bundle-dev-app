<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers;

use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Entity\Role;
use RZ\Roadiz\CoreBundle\Event\Role\PreCreatedRoleEvent;
use RZ\Roadiz\CoreBundle\Event\Role\PreDeletedRoleEvent;
use RZ\Roadiz\CoreBundle\Event\Role\PreUpdatedRoleEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;
use Themes\Rozier\Forms\RoleType;

final class RolesController extends AbstractAdminController
{
    protected function supports(PersistableInterface $item): bool
    {
        return $item instanceof Role;
    }

    protected function getNamespace(): string
    {
        return 'role';
    }

    protected function createEmptyItem(Request $request): PersistableInterface
    {
        return new Role('ROLE_EXAMPLE');
    }

    protected function getTemplateFolder(): string
    {
        return '@RoadizRozier/roles';
    }

    protected function getRequiredRole(): string
    {
        return 'ROLE_ACCESS_ROLES';
    }

    protected function getEntityClass(): string
    {
        return Role::class;
    }

    protected function getFormType(): string
    {
        return RoleType::class;
    }

    protected function getDefaultRouteName(): string
    {
        return 'rolesHomePage';
    }

    protected function getEditRouteName(): string
    {
        return 'rolesEditPage';
    }

    protected function getEntityName(PersistableInterface $item): string
    {
        if ($item instanceof Role) {
            return $item->getRole();
        }
        throw new \InvalidArgumentException('Item should be instance of '.$this->getEntityClass());
    }

    protected function getDefaultOrder(Request $request): array
    {
        return ['name' => 'ASC'];
    }

    protected function denyAccessUnlessItemGranted(PersistableInterface $item): void
    {
        if ($item instanceof Role) {
            $this->denyAccessUnlessGranted($item->getRole());
        }
    }

    protected function createCreateEvent(PersistableInterface $item): ?Event
    {
        if ($item instanceof Role) {
            return new PreCreatedRoleEvent($item);
        }

        return null;
    }

    protected function createUpdateEvent(PersistableInterface $item): ?Event
    {
        if ($item instanceof Role) {
            return new PreUpdatedRoleEvent($item);
        }

        return null;
    }

    protected function createDeleteEvent(PersistableInterface $item): ?Event
    {
        if ($item instanceof Role) {
            return new PreDeletedRoleEvent($item);
        }

        return null;
    }
}
