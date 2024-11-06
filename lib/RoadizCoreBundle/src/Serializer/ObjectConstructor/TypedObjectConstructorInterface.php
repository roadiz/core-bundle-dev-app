<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\ObjectConstructor;

use JMS\Serializer\Construction\ObjectConstructorInterface;

interface TypedObjectConstructorInterface extends ObjectConstructorInterface
{
    public const PERSIST_NEW_OBJECTS = 'persist_on_deserialize';
    public const FLUSH_NEW_OBJECTS = 'flush_on_deserialize';
    public const EXCEPTION_ON_EXISTING = 'exception_on_existing';

    public function supports(string $className, array $data): bool;
}
