<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\DataTransformer;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use Symfony\Component\Form\DataTransformerInterface;

class JoinDataTransformer implements DataTransformerInterface
{
    /**
     * @var NodeTypeField
     */
    private NodeTypeField $nodeTypeField;
    private ManagerRegistry $managerRegistry;
    /**
     * @var class-string
     */
    private string $entityClassname;

    /**
     * @param NodeTypeField $nodeTypeField
     * @param ManagerRegistry $managerRegistry
     * @param string $entityClassname
     */
    public function __construct(
        NodeTypeField $nodeTypeField,
        ManagerRegistry $managerRegistry,
        string $entityClassname
    ) {
        $this->nodeTypeField = $nodeTypeField;
        $this->entityClassname = $entityClassname;
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function transform(mixed $value): mixed
    {
        /*
         * If model is already an PersistableInterface
         */
        if (
            !empty($value) &&
            $value instanceof PersistableInterface
        ) {
            return $value->getId();
        } elseif (!empty($value) && is_array($value)) {
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
            return $value;
        }
        return '';
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function reverseTransform(mixed $value): mixed
    {
        if ($this->nodeTypeField->isManyToMany()) {
            $unorderedEntities = $this->managerRegistry->getRepository($this->entityClassname)->findBy([
                'id' => $value,
            ]);
            /*
             * Need to preserve order in POST data
             */
            usort($unorderedEntities, function (PersistableInterface $a, PersistableInterface $b) use ($value) {
                return array_search($a->getId(), $value) -
                    array_search($b->getId(), $value);
            });
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
