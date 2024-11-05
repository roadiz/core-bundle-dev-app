<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\TagTranslation;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @method TagTranslation|null findOneByName(string $query)
 * @method TagTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method TagTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method TagTranslation[]    findAll()
 * @method TagTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends EntityRepository<TagTranslation>
 */
final class TagTranslationRepository extends EntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        EventDispatcherInterface $dispatcher,
    ) {
        parent::__construct($registry, TagTranslation::class, $dispatcher);
    }
}
