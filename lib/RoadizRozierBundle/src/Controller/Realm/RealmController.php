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
    /**
     * @inheritDoc
     */
    protected function supports(PersistableInterface $item): bool
    {
        return $item instanceof RealmInterface;
    }

    /**
     * @inheritDoc
     */
    protected function getNamespace(): string
    {
        return 'realms';
    }

    /**
     * @inheritDoc
     */
    protected function createEmptyItem(Request $request): PersistableInterface
    {
        return new Realm();
    }

    /**
     * @inheritDoc
     */
    protected function getTemplateFolder(): string
    {
        return '@RoadizRozier/realms';
    }

    /**
     * @inheritDoc
     */
    protected function getRequiredRole(): string
    {
        return 'ROLE_ACCESS_REALMS';
    }

    /**
     * @inheritDoc
     */
    protected function getEntityClass(): string
    {
        return Realm::class;
    }

    /**
     * @inheritDoc
     */
    protected function getFormType(): string
    {
        return RealmType::class;
    }

    /**
     * @inheritDoc
     */
    protected function getDefaultRouteName(): string
    {
        return 'realmsHomePage';
    }

    /**
     * @inheritDoc
     */
    protected function getEditRouteName(): string
    {
        return 'realmsEditPage';
    }

    /**
     * @inheritDoc
     */
    protected function getEntityName(PersistableInterface $item): string
    {
        return $item instanceof RealmInterface ? $item->getName() : '';
    }
}
