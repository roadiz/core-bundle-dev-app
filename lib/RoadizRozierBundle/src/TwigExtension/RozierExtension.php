<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\TwigExtension;

use RZ\Roadiz\CoreBundle\Enum\NodeStatus;
use Themes\Rozier\RozierServiceRegistry;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

final class RozierExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private readonly RozierServiceRegistry $rozierServiceRegistry,
    ) {
    }

    public function getGlobals(): array
    {
        return [
            'rozier' => $this->rozierServiceRegistry,
            'nodeStatuses' => NodeStatus::allLabelsAndValues(),
        ];
    }
}
