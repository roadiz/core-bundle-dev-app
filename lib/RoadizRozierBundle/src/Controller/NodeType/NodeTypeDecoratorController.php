<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\NodeType;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Bag\DecoratedNodeTypes;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeDecorator;
use RZ\Roadiz\CoreBundle\Enum\NodeTypeDecoratorProperty;
use RZ\Roadiz\CoreBundle\ListManager\EntityListManagerFactoryInterface;
use RZ\Roadiz\CoreBundle\Security\LogTrail;
use RZ\Roadiz\RozierBundle\Controller\AbstractAdminController;
use RZ\Roadiz\RozierBundle\Form\NodeTypeDecoratorType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class NodeTypeDecoratorController extends AbstractAdminController
{
    public function __construct(
        private readonly DecoratedNodeTypes $nodeTypesBag,
        UrlGeneratorInterface $urlGenerator,
        EntityListManagerFactoryInterface $entityListManagerFactory,
        ManagerRegistry $managerRegistry,
        TranslatorInterface $translator,
        LogTrail $logTrail,
        EventDispatcherInterface $eventDispatcher,
    ) {
        parent::__construct($urlGenerator, $entityListManagerFactory, $managerRegistry, $translator, $logTrail, $eventDispatcher);
    }

    #[\Override]
    protected function supports(PersistableInterface $item): bool
    {
        return $item instanceof NodeTypeDecorator;
    }

    #[\Override]
    protected function getNamespace(): string
    {
        return 'node-types-decorators';
    }

    #[\Override]
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

    #[\Override]
    protected function getTemplateFolder(): string
    {
        return '@RoadizRozier/node-types-decorators';
    }

    #[\Override]
    protected function getRequiredRole(): string
    {
        return 'ROLE_ACCESS_NODETYPES';
    }

    #[\Override]
    protected function getEntityClass(): string
    {
        return NodeTypeDecorator::class;
    }

    #[\Override]
    protected function getFormType(): string
    {
        return NodeTypeDecoratorType::class;
    }

    #[\Override]
    protected function getDefaultOrder(Request $request): array
    {
        return ['path' => 'ASC'];
    }

    #[\Override]
    protected function getDefaultRouteName(): string
    {
        return 'nodeTypeDecoratorsListPage';
    }

    #[\Override]
    protected function getEditRouteName(): string
    {
        return 'nodeTypeDecoratorsEditPage';
    }

    #[\Override]
    protected function getEntityName(PersistableInterface $item): string
    {
        if ($item instanceof NodeTypeDecorator) {
            return (string) $item;
        }
        throw new \InvalidArgumentException('Item should be instance of '.$this->getEntityClass());
    }

    #[\Override]
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
