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
    protected function supports(PersistableInterface $item): bool
    {
        return $item instanceof RealmInterface;
    }

    protected function getNamespace(): string
    {
        return 'realms';
    }

    protected function createEmptyItem(Request $request): PersistableInterface
    {
        return new Realm();
    }

    protected function getTemplateFolder(): string
    {
        return '@RoadizRozier/realms';
    }

    protected function getRequiredRole(): string
    {
        return 'ROLE_ACCESS_REALMS';
    }

    protected function getEntityClass(): string
    {
        return Realm::class;
    }

    protected function getFormType(): string
    {
        return RealmType::class;
    }

    protected function getDefaultRouteName(): string
    {
        return 'realmsHomePage';
    }

    protected function getEditRouteName(): string
    {
        return 'realmsEditPage';
    }

    protected function getEntityName(PersistableInterface $item): string
    {
        return $item instanceof RealmInterface ? $item->getName() : '';
    }
}
