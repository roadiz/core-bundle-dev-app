<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Explorer;

interface ExplorerItemFactoryInterface
{
    public function createForEntity(mixed $entity, array $configuration = []): ExplorerItemInterface;
}
