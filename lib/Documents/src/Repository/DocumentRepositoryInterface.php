<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Repository;

use Doctrine\Persistence\ObjectRepository;
use RZ\Roadiz\Documents\Models\DocumentInterface;

/**
 * @template T of DocumentInterface
 *
 * @template-extends ObjectRepository<T>
 *
 * @extends ObjectRepository<T>
 */
interface DocumentRepositoryInterface extends ObjectRepository
{
    /**
     * @return array<T>
     */
    public function findAllUnused(): array;

    /**
     * @return array<T>
     */
    public function findDuplicates(): array;

    /**
     * @return array<T>
     */
    public function findAllWithoutFileHash(): array;

    /**
     * @return T|null
     */
    public function findOneByHashAndAlgorithm(string $hash, string $hashAlgorithm): ?DocumentInterface;
}
