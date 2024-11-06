<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers\Attributes;

use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Entity\AttributeGroup;
use RZ\Roadiz\CoreBundle\Form\AttributeGroupType;
use Symfony\Component\HttpFoundation\Request;
use Themes\Rozier\Controllers\AbstractAdminController;

class AttributeGroupController extends AbstractAdminController
{
    protected function supports(PersistableInterface $item): bool
    {
        return $item instanceof AttributeGroup;
    }

    protected function getNamespace(): string
    {
        return 'attribute_group';
    }

    protected function createEmptyItem(Request $request): PersistableInterface
    {
        return new AttributeGroup();
    }

    protected function getTemplateFolder(): string
    {
        return '@RoadizRozier/attributes/groups';
    }

    protected function getRequiredRole(): string
    {
        return 'ROLE_ACCESS_ATTRIBUTES';
    }

    protected function getEntityClass(): string
    {
        return AttributeGroup::class;
    }

    protected function getFormType(): string
    {
        return AttributeGroupType::class;
    }

    protected function getDefaultRouteName(): string
    {
        return 'attributeGroupsHomePage';
    }

    protected function getEditRouteName(): string
    {
        return 'attributeGroupsEditPage';
    }

    protected function getEntityName(PersistableInterface $item): string
    {
        if ($item instanceof AttributeGroup) {
            return $item->getName();
        }
        throw new \InvalidArgumentException('Item should be instance of '.$this->getEntityClass());
    }

    protected function getDefaultOrder(Request $request): array
    {
        return ['canonicalName' => 'ASC'];
    }
}
