<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\TwigExtension;

use RZ\Roadiz\Core\AbstractEntities\NodeInterface;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Enum\NodeStatus;
use Themes\Rozier\RozierServiceRegistry;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFunction;

final class RozierExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private readonly RozierServiceRegistry $rozierServiceRegistry,
        private readonly NodeTypes $nodeTypesBag,
    ) {
    }

    public function getGlobals(): array
    {
        return [
            'rozier' => $this->rozierServiceRegistry,
            'nodeStatuses' => NodeStatus::allLabelsAndValues(),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('getNodeType', [$this, 'getNodeType']),
        ];
    }

    public function getNodeType(mixed $object): ?NodeType
    {
        if (null === $object) {
            return null;
        }

        if ($object instanceof NodeInterface) {
            return $this->nodeTypesBag->get($object->getNodeTypeName());
        }

        if ($object instanceof NodesSources) {
            return $this->nodeTypesBag->get($object->getNodeTypeName());
        }

        throw new \RuntimeException('Unexpected object type');
    }
}
