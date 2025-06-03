<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ObjectManager;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

readonly class EntityCollectionTransformer implements DataTransformerInterface
{
    /**
     * @param class-string<PersistableInterface> $classname
     */
    public function __construct(
        protected ObjectManager $manager,
        protected string $classname,
        protected bool $asCollection = false,
    ) {
    }

    /**
     * @param iterable<PersistableInterface>|mixed|null $value
     */
    #[\Override]
    public function transform(mixed $value): string|array
    {
        if (empty($value)) {
            return '';
        }
        $ids = [];
        /** @var PersistableInterface $entity */
        foreach ($value as $entity) {
            $ids[] = $entity->getId();
        }
        if ($this->asCollection) {
            return $ids;
        }

        return implode(',', $ids);
    }

    /**
     * @param string|array|null $value
     *
     * @return array<PersistableInterface>|ArrayCollection<int, PersistableInterface>
     */
    #[\Override]
    public function reverseTransform(mixed $value): array|ArrayCollection
    {
        if (!$value) {
            if ($this->asCollection) {
                return new ArrayCollection();
            }

            return [];
        }

        if (is_array($value)) {
            $ids = $value;
        } else {
            $ids = explode(',', $value);
        }

        /** @var array<PersistableInterface> $entities */
        $entities = [];
        foreach ($ids as $entityId) {
            /** @var PersistableInterface|null $entity */
            $entity = $this->manager
                ->getRepository($this->classname)
                ->find($entityId)
            ;
            if (null === $entity) {
                throw new TransformationFailedException(sprintf('A %s with id "%s" does not exist!', $this->classname, $entityId));
            }

            $entities[] = $entity;
        }
        if ($this->asCollection) {
            return new ArrayCollection($entities);
        }

        return $entities;
    }
}
