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
    #[\Override]
    public function getRepository(): TagRepository
    {
        return $this->managerRegistry->getRepository(Tag::class);
    }

    /**
     * Get tags using criteria, orders, limit and offset.
     *
     * When no order is defined, tags are ordered by position.
     *
     * @return array<Tag>|Paginator<Tag>
     */
    #[\Override]
    public function getBy(
        array $criteria,
        ?array $order = null,
        ?int $limit = null,
        ?int $offset = null,
    ): array|Paginator {
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

    #[\Override]
    public function countBy(array $criteria): int
    {
        return $this->getRepository()
                    ->countBy(
                        $criteria,
                        null
                    );
    }

    #[\Override]
    public function getOneBy(array $criteria, ?array $order = null): ?Tag
    {
        return $this->getRepository()
                    ->findOneBy(
                        $criteria,
                        $order,
                        null
                    );
    }
}
