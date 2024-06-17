<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\AttributeValue;
use RZ\Roadiz\CoreBundle\Model\AttributableInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @extends EntityRepository<AttributeValue>
 */
final class AttributeValueRepository extends EntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        EventDispatcherInterface $dispatcher
    ) {
        parent::__construct($registry, AttributeValue::class, $dispatcher);
    }

    /**
     * @param AttributableInterface $attributable
     * @param bool $orderByWeight
     * @return array<AttributeValue>
     */
    public function findByAttributable(
        AttributableInterface $attributable,
        bool $orderByWeight = false
    ): array {
        $qb = $this->createQueryBuilder('av');
        $qb = $qb->addSelect('avt')
            ->addSelect('a')
            ->addSelect('at')
            ->addSelect('ad')
            ->addSelect('ag')
            ->addSelect('agt')
            // We need to fetch values without translations too
            ->leftJoin('av.attributeValueTranslations', 'avt')
            ->innerJoin('av.attribute', 'a')
            ->leftJoin('a.attributeDocuments', 'ad')
            ->leftJoin('a.attributeTranslations', 'at')
            ->leftJoin('a.group', 'ag')
            ->leftJoin('ag.attributeGroupTranslations', 'agt')
            ->andWhere($qb->expr()->eq('av.node', ':attributable'))
            ->setParameters([
                'attributable' => $attributable,
            ])
            ->setCacheable(true);

        if ($orderByWeight) {
            $qb->addOrderBy('a.weight', 'DESC');
        } else {
            $qb->addOrderBy('av.position', 'ASC');
        }

        return $qb->getQuery()
            ->getResult();
    }

    /**
     * @param AttributableInterface $attributable
     * @param TranslationInterface  $translation
     *
     * @return array
     */
    public function findByAttributableAndTranslation(
        AttributableInterface $attributable,
        TranslationInterface $translation
    ): array {
        $qb = $this->createQueryBuilder('av');
        return $qb->addSelect('avt')
            ->addSelect('a')
            ->addSelect('at')
            ->addSelect('ad')
            ->addSelect('ag')
            ->addSelect('agt')
            ->innerJoin('av.attributeValueTranslations', 'avt')
            ->innerJoin('av.attribute', 'a')
            ->leftJoin('a.attributeTranslations', 'at')
            ->leftJoin('a.attributeDocuments', 'ad')
            ->leftJoin('a.group', 'ag')
            ->leftJoin('ag.attributeGroupTranslations', 'agt')
            ->andWhere($qb->expr()->eq('av.node', ':attributable'))
            ->andWhere($qb->expr()->eq('at.translation', ':translation'))
            ->andWhere($qb->expr()->eq('agt.translation', ':translation'))
            ->addOrderBy('av.position', 'ASC')
            ->setParameters([
                'attributable' => $attributable,
                'translation' => $translation
            ])
            ->setCacheable(true)
            ->getQuery()
            ->getResult();
    }
}
