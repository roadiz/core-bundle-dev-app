<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\DataTransformer;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use Symfony\Component\Form\DataTransformerInterface;

final readonly class JoinDataTransformer implements DataTransformerInterface
{
    /**
     * @param class-string $entityClassname
     */
    public function __construct(
        private NodeTypeField $nodeTypeField,
        private ManagerRegistry $managerRegistry,
        private string $entityClassname,
    ) {
    }

    /**
     * @return array joinDataTransformer must always return an array for view data
     */
    #[\Override]
    public function transform(mixed $value): array
    {
        /*
         * If model is already an PersistableInterface
         */
        if (
            !empty($value)
            && $value instanceof PersistableInterface
        ) {
            return [$value->getId()];
        } elseif (!empty($value) && is_iterable($value)) {
            /*
             * If model is a collection of AbstractEntity
             */
            $idArray = [];
            foreach ($value as $entity) {
                if ($entity instanceof PersistableInterface) {
                    $idArray[] = $entity->getId();
                }
            }

            return $idArray;
        } elseif (!empty($value)) {
            return [$value];
        }

        return [];
    }

    #[\Override]
    public function reverseTransform(mixed $value): array|object|null
    {
        if ($this->nodeTypeField->isManyToMany()) {
            /** @var PersistableInterface[] $unorderedEntities */
            $unorderedEntities = $this->managerRegistry->getRepository($this->entityClassname)->findBy([
                'id' => $value,
            ]);
            /*
             * Need to preserve order in POST data
             */
            usort($unorderedEntities, fn (PersistableInterface $a, PersistableInterface $b) => array_search($a->getId(), $value) -
                array_search($b->getId(), $value));

            return $unorderedEntities;
        }
        if ($this->nodeTypeField->isManyToOne()) {
            return $this->managerRegistry->getRepository($this->entityClassname)->findOneBy([
                'id' => $value,
            ]);
        }

        return null;
    }
}
