<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Attribute;

use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Entity\AttributeGroup;
use RZ\Roadiz\CoreBundle\Form\AttributeGroupType;
use RZ\Roadiz\RozierBundle\Controller\AbstractAdminController;
use Symfony\Component\HttpFoundation\Request;

final class AttributeGroupController extends AbstractAdminController
{
    #[\Override]
    protected function supports(PersistableInterface $item): bool
    {
        return $item instanceof AttributeGroup;
    }

    #[\Override]
    protected function getNamespace(): string
    {
        return 'attribute_group';
    }

    #[\Override]
    protected function createEmptyItem(Request $request): PersistableInterface
    {
        return new AttributeGroup();
    }

    #[\Override]
    protected function getTemplateFolder(): string
    {
        return '@RoadizRozier/attributes/groups';
    }

    #[\Override]
    protected function getRequiredRole(): string
    {
        return 'ROLE_ACCESS_ATTRIBUTES';
    }

    #[\Override]
    protected function getEntityClass(): string
    {
        return AttributeGroup::class;
    }

    #[\Override]
    protected function getFormType(): string
    {
        return AttributeGroupType::class;
    }

    #[\Override]
    protected function getDefaultRouteName(): string
    {
        return 'attributeGroupsHomePage';
    }

    #[\Override]
    protected function getEditRouteName(): string
    {
        return 'attributeGroupsEditPage';
    }

    #[\Override]
    protected function getEntityName(PersistableInterface $item): string
    {
        if ($item instanceof AttributeGroup) {
            return $item->getName();
        }
        throw new \InvalidArgumentException('Item should be instance of '.$this->getEntityClass());
    }

    #[\Override]
    protected function getDefaultOrder(Request $request): array
    {
        return ['canonicalName' => 'ASC'];
    }
}
