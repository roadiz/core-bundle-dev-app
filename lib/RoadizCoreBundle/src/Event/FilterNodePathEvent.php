<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Event;

use RZ\Roadiz\CoreBundle\Entity\Node;

/**
 * @package RZ\Roadiz\CoreBundle\Event
 */
abstract class FilterNodePathEvent extends FilterNodeEvent
{
    protected array $paths;
    protected ?\DateTime $updatedAt;

    /**
     * @param Node           $node
     * @param array          $paths
     * @param \DateTime|null $updatedAt
     */
    public function __construct(Node $node, array $paths = [], \DateTime $updatedAt = null)
    {
        parent::__construct($node);
        $this->paths = $paths;
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return array
     */
    public function getPaths(): array
    {
        return $this->paths;
    }

    /**
     * @return \DateTime|null
     */
    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }
}
