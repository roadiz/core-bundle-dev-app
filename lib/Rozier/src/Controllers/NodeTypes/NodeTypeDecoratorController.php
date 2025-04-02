<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers\NodeTypes;

use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Bag\DecoratedNodeTypes;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeDecorator;
use RZ\Roadiz\CoreBundle\Enum\NodeTypeDecoratorProperty;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Themes\Rozier\Controllers\AbstractAdminController;
use Themes\Rozier\Forms\NodeTypeDecoratorType;

final class NodeTypeDecoratorController extends AbstractAdminController
{
    public function __construct(
        private readonly DecoratedNodeTypes $nodeTypesBag,
        UrlGeneratorInterface $urlGenerator,
    ) {
        parent::__construct($urlGenerator);
    }

    protected function supports(PersistableInterface $item): bool
    {
        return $item instanceof NodeTypeDecorator;
    }

    protected function getNamespace(): string
    {
        return 'node-types-decorators';
    }

    protected function createEmptyItem(Request $request): PersistableInterface
    {
        /** @var string $nodeTypeName */
        $nodeTypeName = $request->get('nodeTypeName');
        $nodeTypeFieldName = $request->request->get('nodeTypeFieldName') ?? $request->query->get('nodeTypeFieldName');
        $nodeType = $this->nodeTypesBag->get($nodeTypeName);
        $nodeTypeField = null;
        if ($nodeTypeFieldName && is_string($nodeTypeFieldName)) {
            $nodeTypeField = $this->nodeTypesBag->get($nodeTypeName)->getFieldByName($nodeTypeFieldName);
        }
        if (null !== $nodeTypeField) {
            $property = NodeTypeDecoratorProperty::NODE_TYPE_FIELD_LABEL;
        } else {
            $property = NodeTypeDecoratorProperty::NODE_TYPE_DESCRIPTION;
        }

        return NodeTypeDecorator::withNodeType(
            $nodeType,
            $nodeTypeField,
            $property,
        );
    }

    protected function getTemplateFolder(): string
    {
        return '@RoadizRozier/node-types-decorators';
    }

    protected function getRequiredRole(): string
    {
        return 'ROLE_ACCESS_NODETYPES';
    }

    protected function getEntityClass(): string
    {
        return NodeTypeDecorator::class;
    }

    protected function getFormType(): string
    {
        return NodeTypeDecoratorType::class;
    }

    protected function getDefaultOrder(Request $request): array
    {
        return ['path' => 'ASC'];
    }

    protected function getDefaultRouteName(): string
    {
        return 'nodeTypeDecoratorsListPage';
    }

    protected function getEditRouteName(): string
    {
        return 'nodeTypeDecoratorsEditPage';
    }

    protected function getEntityName(PersistableInterface $item): string
    {
        if ($item instanceof NodeTypeDecorator) {
            return (string) $item;
        }
        throw new \InvalidArgumentException('Item should be instance of '.$this->getEntityClass());
    }

    protected function additionalAssignation(Request $request): void
    {
        parent::additionalAssignation($request);

        $this->assignation['nodeTypes'] = $this->nodeTypesBag->all();

        $nodeTypeName = $request->get('nodeTypeName', null);
        if (null === $nodeTypeName) {
            return;
        }
        $nodeTypeFieldName = $request->request->get('nodeTypeFieldName') ?? $request->query->get('nodeTypeFieldName');
        $this->assignation['nodeType'] = $this->nodeTypesBag->get($nodeTypeName);
        if ($nodeTypeFieldName && is_string($nodeTypeFieldName)) {
            $this->assignation['nodeTypeField'] = $this->nodeTypesBag->get($nodeTypeName)?->getFieldByName($nodeTypeFieldName);
        }
    }
}
