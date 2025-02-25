<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers\NodeTypes;

use JMS\Serializer\SerializerInterface;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Bag\DecoratedNodeTypes;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeDecorator;
use RZ\Roadiz\CoreBundle\Enum\NodeTypeDecoratorProperty;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Themes\Rozier\Controllers\AbstractAdminController;
use Themes\Rozier\Forms\NodeTypeDecoratorType;

final class NodeTypeDecoratorController extends AbstractAdminController
{
    public function __construct(
        private readonly DecoratedNodeTypes $nodeTypesBag,
        SerializerInterface $serializer,
        UrlGeneratorInterface $urlGenerator,
    ) {
        parent::__construct($serializer, $urlGenerator);
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
        $nodeTypeFieldName = $request->request->get('nodeTypeFieldName');
        $nodeType = $this->nodeTypesBag->get($nodeTypeName);
        $nodeTypeField = null;
        if ($nodeTypeFieldName && is_string($nodeTypeFieldName)) {
            $nodeTypeField = $this->nodeTypesBag->get($nodeTypeName)->getFieldByName($nodeTypeFieldName);
        }
        if (null === $nodeTypeField) {
            $property = NodeTypeDecoratorProperty::NODE_TYPE_DESCRIPTION;
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

    /**
     * @throws \Twig\Error\RuntimeError
     */
    public function defaultAction(Request $request): ?Response
    {
        $this->assignation['nodeTypes'] = $this->nodeTypesBag->all();

        return parent::defaultAction($request);
    }
}
