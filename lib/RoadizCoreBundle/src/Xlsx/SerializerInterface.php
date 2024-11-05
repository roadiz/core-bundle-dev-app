<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Xlsx;

/**
 * @deprecated XLSX serialization is deprecated and will be removed in next major version.
 * EntitySerializer that implements simple serialization/deserialization methods.
 */
interface SerializerInterface
{
    /**
     * Serializes data.
     */
    public function serialize(mixed $obj): string;

    /**
     * Create a simple associative array with an entity.
     */
    public function toArray(mixed $obj): array;

    /**
     * Deserializes a json file into a readable array of data.
     *
     * @param string $string Input to deserialize
     */
    public function deserialize(string $string): mixed;
}
