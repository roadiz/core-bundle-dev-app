<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EntityApi;

use Doctrine\ORM\Tools\Pagination\Paginator;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\CoreBundle\Repository\TagRepository;

/**
 * @deprecated Use TagRepository directly
 */
class TagApi extends AbstractApi
{
    /**
     * @return TagRepository
     */
    public function getRepository()
    {
        return $this->managerRegistry->getRepository(Tag::class);
    }

    /**
     * Get tags using criteria, orders, limit and offset.
     *
     * When no order is defined, tags are ordered by position.
     *
     * @param array      $criteria
     * @param array|null $order
     * @param int|null   $limit
     * @param int|null   $offset
     *
     * @return array|Paginator
     */
    public function getBy(
        array $criteria,
        array $order = null,
        ?int $limit = null,
        ?int $offset = null
    ) {
        if (null === $order) {
            $order = [
                'position' => 'ASC',
            ];
        }

        return $this->getRepository()
                    ->findBy(
                        $criteria,
                        $order,
                        $limit,
                        $offset,
                        null
                    );
    }
    /**
     * {@inheritdoc}
     */
    public function countBy(array $criteria)
    {
        return $this->getRepository()
                    ->countBy(
                        $criteria,
                        null
                    );
    }
    /**
     * {@inheritdoc}
     */
    public function getOneBy(array $criteria, array $order = null)
    {
        return $this->getRepository()
                    ->findOneBy(
                        $criteria,
                        $order,
                        null
                    );
    }
}
