<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller;

use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Entity\SettingGroup;
use RZ\Roadiz\RozierBundle\Form\SettingGroupType;
use Symfony\Component\HttpFoundation\Request;

final class SettingGroupController extends AbstractAdminController
{
    #[\Override]
    protected function supports(PersistableInterface $item): bool
    {
        return $item instanceof SettingGroup;
    }

    #[\Override]
    protected function getNamespace(): string
    {
        return 'settingGroup';
    }

    #[\Override]
    protected function createEmptyItem(Request $request): PersistableInterface
    {
        return new SettingGroup();
    }

    #[\Override]
    protected function getTemplateFolder(): string
    {
        return '@RoadizRozier/settingGroups';
    }

    #[\Override]
    protected function getRequiredRole(): string
    {
        return 'ROLE_ACCESS_SETTINGS';
    }

    #[\Override]
    protected function getEntityClass(): string
    {
        return SettingGroup::class;
    }

    #[\Override]
    protected function getFormType(): string
    {
        return SettingGroupType::class;
    }

    #[\Override]
    protected function getDefaultRouteName(): string
    {
        return 'settingGroupsHomePage';
    }

    #[\Override]
    protected function getEditRouteName(): string
    {
        return 'settingGroupsEditPage';
    }

    #[\Override]
    protected function getEntityName(PersistableInterface $item): string
    {
        if ($item instanceof SettingGroup) {
            return $item->getName();
        }
        throw new \InvalidArgumentException('Item should be instance of '.$this->getEntityClass());
    }

    #[\Override]
    protected function getDefaultOrder(Request $request): array
    {
        return ['name' => 'ASC'];
    }
}
