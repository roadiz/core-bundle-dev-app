<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\DataTransformer;

use Doctrine\ORM\EntityManagerInterface;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Transform Doctrine integer ID to their Doctrine entities.
 */
final readonly class ReversePersistableTransformer implements DataTransformerInterface
{
    /**
     * @param class-string<PersistableInterface> $doctrineEntity
     */
    public function __construct(private EntityManagerInterface $entityManager, private string $doctrineEntity)
    {
    }

    #[\Override]
    public function transform(mixed $value): ?array
    {
        if (null === $value) {
            return null;
        }

        return $this->entityManager->getRepository($this->doctrineEntity)->findBy([
            'id' => $value,
        ]);
    }

    #[\Override]
    public function reverseTransform(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map(fn (PersistableInterface $item) => $item->getId(), $value);
        }
        if ($value instanceof PersistableInterface) {
            return $value->getId();
        }

        return null;
    }
}
