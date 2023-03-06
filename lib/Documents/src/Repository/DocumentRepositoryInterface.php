<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Repository;

use Doctrine\Persistence\ObjectRepository;

/**
 * @template T of \RZ\Roadiz\Documents\Models\DocumentInterface
 * @template-extends ObjectRepository<T>
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
}
