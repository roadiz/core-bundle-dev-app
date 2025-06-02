<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use RZ\Roadiz\UserBundle\Api\Dto\VoidOutput;

final class VoidUserProvider implements ProviderInterface
{
    #[\Override]
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): VoidOutput
    {
        return new VoidOutput();
    }
}
