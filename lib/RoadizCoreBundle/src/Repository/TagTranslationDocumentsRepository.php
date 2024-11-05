<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\TagTranslation;
use RZ\Roadiz\CoreBundle\Entity\TagTranslationDocuments;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @method TagTranslationDocuments|null findOneByName(string $query)
 * @method TagTranslationDocuments|null find($id, $lockMode = null, $lockVersion = null)
 * @method TagTranslationDocuments|null findOneBy(array $criteria, array $orderBy = null)
 * @method TagTranslationDocuments[]    findAll()
 * @method TagTranslationDocuments[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends EntityRepository<TagTranslationDocuments>
 */
final class TagTranslationDocumentsRepository extends EntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        EventDispatcherInterface $dispatcher,
    ) {
        parent::__construct($registry, TagTranslationDocuments::class, $dispatcher);
    }

    /**
     * @param TagTranslation $tagTranslation
     *
     * @return int
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getLatestPosition($tagTranslation)
    {
        $query = $this->_em->createQuery('SELECT MAX(ttd.position)
FROM RZ\Roadiz\CoreBundle\Entity\TagTranslationDocuments ttd
WHERE ttd.tagTranslation = :tagTranslation')
                    ->setParameter('tagTranslation', $tagTranslation);

        return (int) $query->getSingleScalarResult();
    }
}
