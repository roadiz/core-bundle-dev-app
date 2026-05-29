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
            if (empty($value) || !is_array($value)) {
                return [];
            }
            /** @var PersistableInterface[] $unorderedEntities */
            $unorderedEntities = $this->managerRegistry->getRepository($this->entityClassname)->findBy([
                'id' => $value,
            ]);
            /*
             * Need to preserve order in POST data
             */
            $stringIds = array_map(strval(...), $value);
            usort($unorderedEntities, function (PersistableInterface $a, PersistableInterface $b) use ($stringIds): int {
                $aPosition = array_search((string) $a->getId(), $stringIds, true);
                $bPosition = array_search((string) $b->getId(), $stringIds, true);

                return ((int) $aPosition) <=> ((int) $bPosition);
            });

            return $unorderedEntities;
        }
        if ($this->nodeTypeField->isManyToOne()) {
            if (empty($value)) {
                return null;
            }

            return $this->managerRegistry->getRepository($this->entityClassname)->findOneBy([
                'id' => $value,
            ]);
        }

        return null;
    }
}
