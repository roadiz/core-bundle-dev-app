<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\Repository;

use Doctrine\ORM\EntityRepository;
use RZ\Roadiz\UserBundle\Entity\UserValidationToken;

class UserValidationTokenRepository extends EntityRepository
{
    public function deleteAllExpired(): int
    {
        $qb = $this->createQueryBuilder('t');
        $qb->delete()
            ->andWhere($qb->expr()->lt('t.tokenValidUntil', ':now'))
            ->setParameter(':now', new \DateTime());

        return $qb->getQuery()->getResult() ?? 0;
    }

    public function findOneByValidToken(string $token): ?UserValidationToken
    {
        $qb = $this->createQueryBuilder('t');
        return $qb->andWhere($qb->expr()->eq('t.token', ':token'))
            ->andWhere($qb->expr()->gte('t.tokenValidUntil', ':now'))
            ->setParameter('token', $token)
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getOneOrNullResult();
    }
}
