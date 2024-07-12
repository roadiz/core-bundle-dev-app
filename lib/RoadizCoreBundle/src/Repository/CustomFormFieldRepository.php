<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\CustomForm;
use RZ\Roadiz\CoreBundle\Entity\CustomFormField;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @extends EntityRepository<CustomFormField>
 */
final class CustomFormFieldRepository extends EntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        EventDispatcherInterface $dispatcher
    ) {
        parent::__construct($registry, CustomFormField::class, $dispatcher);
    }

    public function findDistinctGroupNamesInCustomForm(CustomForm $customForm): array
    {
        $qb = $this->createQueryBuilder('o');
        $qb->select('DISTINCT o.groupName')
            ->andWhere($qb->expr()->eq('o.customForm', ':customForm'))
            ->setParameter('customForm', $customForm);

        $result = $qb->getQuery()->getResult();
        return array_map(fn (array $row) => $row['groupName'], $result);
    }
}
