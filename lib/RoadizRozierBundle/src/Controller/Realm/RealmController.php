<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Realm;

use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Entity\Realm;
use RZ\Roadiz\CoreBundle\Form\RealmType;
use RZ\Roadiz\CoreBundle\Model\RealmInterface;
use Symfony\Component\HttpFoundation\Request;
use Themes\Rozier\Controllers\AbstractAdminWithBulkController;

class RealmController extends AbstractAdminWithBulkController
{
    #[\Override]
    protected function supports(PersistableInterface $item): bool
    {
        return $item instanceof RealmInterface;
    }

    #[\Override]
    protected function getNamespace(): string
    {
        return 'realms';
    }

    #[\Override]
    protected function createEmptyItem(Request $request): PersistableInterface
    {
        return new Realm();
    }

    #[\Override]
    protected function getTemplateFolder(): string
    {
        return '@RoadizRozier/realms';
    }

    #[\Override]
    protected function getRequiredRole(): string
    {
        return 'ROLE_ACCESS_REALMS';
    }

    #[\Override]
    protected function getEntityClass(): string
    {
        return Realm::class;
    }

    #[\Override]
    protected function getFormType(): string
    {
        return RealmType::class;
    }

    #[\Override]
    protected function getDefaultRouteName(): string
    {
        return 'realmsHomePage';
    }

    #[\Override]
    protected function getEditRouteName(): string
    {
        return 'realmsEditPage';
    }

    #[\Override]
    protected function getEntityName(PersistableInterface $item): string
    {
        return $item instanceof RealmInterface ? $item->getName() : '';
    }
}
