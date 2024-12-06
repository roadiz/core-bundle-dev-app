<?php

declare(strict_types=1);

namespace RZ\Roadiz\FontBundle\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Repository\EntityRepository;
use RZ\Roadiz\FontBundle\Entity\Font;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @extends EntityRepository<Font>
 */
final class FontRepository extends EntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        EventDispatcherInterface $dispatcher,
    ) {
        parent::__construct($registry, Font::class, $dispatcher);
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getLatestUpdateDate(): ?\DateTimeInterface
    {
        $query = $this->_em->createQuery('
            SELECT MAX(f.updatedAt) FROM RZ\Roadiz\FontBundle\Entity\Font f');
        $updatedAt = $query->getSingleScalarResult();

        return \is_string($updatedAt) ? new \DateTimeImmutable($updatedAt) : null;
    }
}
