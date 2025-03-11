<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\TwigExtension;

use RZ\Roadiz\Core\AbstractEntities\NodeInterface;
use RZ\Roadiz\CoreBundle\Bag\DecoratedNodeTypes;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Entity\StackType;
use RZ\Roadiz\CoreBundle\Enum\NodeStatus;
use Themes\Rozier\RozierServiceRegistry;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFunction;

final class RozierExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private readonly RozierServiceRegistry $rozierServiceRegistry,
        private readonly DecoratedNodeTypes $nodeTypesBag,
    ) {
    }

    public function getGlobals(): array
    {
        return [
            'rozier' => $this->rozierServiceRegistry,
            'nodeStatuses' => NodeStatus::allLabelsAndValues(),
            'thumbnailFormat' => [
                'quality' => 50,
                'fit' => '128x128',
                'sharpen' => 5,
                'inline' => false,
                'picture' => true,
                'controls' => false,
                'loading' => 'lazy',
            ],
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

        if (is_string($object)) {
            return $this->nodeTypesBag->get($object);
        }

        if ($object instanceof StackType) {
            return $this->nodeTypesBag->get($object->getNodeTypeName());
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
