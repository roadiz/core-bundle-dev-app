<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers;

use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Entity\SettingGroup;
use Symfony\Component\HttpFoundation\Request;
use Themes\Rozier\Forms\SettingGroupType;

final class SettingGroupsController extends AbstractAdminController
{
    protected function supports(PersistableInterface $item): bool
    {
        return $item instanceof SettingGroup;
    }

    protected function getNamespace(): string
    {
        return 'settingGroup';
    }

    protected function createEmptyItem(Request $request): PersistableInterface
    {
        return new SettingGroup();
    }

    protected function getTemplateFolder(): string
    {
        return '@RoadizRozier/settingGroups';
    }

    protected function getRequiredRole(): string
    {
        return 'ROLE_ACCESS_SETTINGS';
    }

    protected function getEntityClass(): string
    {
        return SettingGroup::class;
    }

    protected function getFormType(): string
    {
        return SettingGroupType::class;
    }

    protected function getDefaultRouteName(): string
    {
        return 'settingGroupsHomePage';
    }

    protected function getEditRouteName(): string
    {
        return 'settingGroupsEditPage';
    }

    protected function getEntityName(PersistableInterface $item): string
    {
        if ($item instanceof SettingGroup) {
            return $item->getName();
        }
        throw new \InvalidArgumentException('Item should be instance of '.$this->getEntityClass());
    }

    protected function getDefaultOrder(Request $request): array
    {
        return ['name' => 'ASC'];
    }
}
