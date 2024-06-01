<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\DataTransformer;

use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Api\Model\WebResponseInterface;

interface WebResponseDataTransformerInterface
{
    /**
     * @template T of PersistableInterface
     * @param T $object
     * @param string $to
     * @param array $context
     * @return WebResponseInterface<T>|null
     */
    public function transform(PersistableInterface $object, string $to, array $context = []): ?WebResponseInterface;

    public function createWebResponse(): WebResponseInterface;
}
