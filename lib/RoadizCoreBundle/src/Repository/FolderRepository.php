<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\Folder;
use RZ\Roadiz\CoreBundle\Entity\FolderTranslation;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\Utils\StringHandler;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @extends EntityRepository<Folder>
 */
final class FolderRepository extends EntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        EventDispatcherInterface $dispatcher
    ) {
        parent::__construct($registry, Folder::class, $dispatcher);
    }

    /**
     * Find a folder according to the given path or create it.
     *
     * @param string $folderPath
     * @param TranslationInterface|null $translation
     *
     * @return Folder|null
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function findOrCreateByPath(string $folderPath, ?TranslationInterface $translation = null): ?Folder
    {
        $folderPath = trim($folderPath);
        $folders = explode('/', $folderPath);
        $folders = array_filter($folders);

        if (count($folders) === 0) {
            return null;
        }

        $folderName = $folders[count($folders) - 1];
        $folder = $this->findOneByFolderName($folderName);

        if (null === $folder) {
            /*
             * Creation of a new folder
             * before linking it to the node
             */
            $parentFolder = null;

            if (count($folders) > 1) {
                $parentName = $folders[count($folders) - 2];
                $parentFolder = $this->findOneByFolderName($parentName);
            }
            $folder = new Folder();
            $folder->setFolderName($folderName);

            if (null !== $parentFolder) {
                $folder->setParent($parentFolder);
            }

            /*
             * Add folder translation
             * with given name
             */
            if (null === $translation) {
                $translation = $this->_em->getRepository(Translation::class)->findDefault();
            }
            $folderTranslation = new FolderTranslation($folder, $translation);
            $folderTranslation->setName($folderName);

            $this->_em->persist($folder);
            $this->_em->persist($folderTranslation);
            $this->_em->flush();
        }

        return $folder;
    }

    /**
     * Find a folder according to the given path.
     *
     * @param string $folderPath
     *
     * @return Folder|null
     * @throws NonUniqueResultException
     */
    public function findByPath(string $folderPath): ?Folder
    {
        $folderPath = trim($folderPath);

        $folders = explode('/', $folderPath);
        $folders = array_filter($folders);
        $folderName = $folders[count($folders) - 1];

        return $this->findOneByFolderName($folderName);
    }

    /**
     * @param Folder $folder
     * @param TranslationInterface|null $translation
     * @return array
     */
    public function findAllChildrenFromFolder(Folder $folder, TranslationInterface $translation = null): array
    {
        $ids = $this->findAllChildrenIdFromFolder($folder);
        if (count($ids) > 0) {
            $qb = $this->createQueryBuilder('f');
            $qb->addSelect('f')
                ->andWhere($qb->expr()->in('f.id', ':ids'))
                ->setParameter(':ids', $ids);

            if (null !== $translation) {
                $qb->addSelect('tf')
                    ->leftJoin('f.translatedFolders', 'tf')
                    ->andWhere($qb->expr()->eq('tf.translation', ':translation'))
                    ->setParameter(':translation', $translation);
            }
            return $qb->getQuery()->getResult();
        }
        return [];
    }

    /**
     * @param string $folderName
     * @param TranslationInterface|null $translation
     *
     * @return Folder|null
     * @throws NonUniqueResultException
     */
    public function findOneByFolderName(string $folderName, TranslationInterface $translation = null): ?Folder
    {
        $qb = $this->createQueryBuilder('f');
        $qb->addSelect('f')
            ->andWhere($qb->expr()->in('f.folderName', ':name'))
            ->setMaxResults(1)
            ->setParameter(':name', StringHandler::slugify($folderName));

        if (null !== $translation) {
            $qb->addSelect('tf')
                ->leftJoin('f.translatedFolders', 'tf')
                ->andWhere($qb->expr()->eq('tf.translation', ':translation'))
                ->setParameter(':translation', $translation);
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param Folder $folder
     * @return array
     */
    public function findAllChildrenIdFromFolder(Folder $folder): array
    {
        $idsArray = $this->findChildrenIdFromFolder($folder);

        /** @var Folder $child */
        foreach ($folder->getChildren() as $child) {
            $idsArray = array_merge($idsArray, $this->findAllChildrenIdFromFolder($child));
        }

        return $idsArray;
    }

    /**
     * @param Folder $folder
     * @return array
     */
    public function findChildrenIdFromFolder(Folder $folder): array
    {
        $qb = $this->createQueryBuilder('f');
        $qb->select('f.id')
            ->where($qb->expr()->eq('f.parent', ':parent'))
            ->setParameter(':parent', $folder);

        return array_map('current', $qb->getQuery()->getScalarResult());
    }

    /**
     * Create a Criteria object from a search pattern and additionnal fields.
     *
     * @param string $pattern Search pattern
     * @param QueryBuilder $qb QueryBuilder to pass
     * @param array $criteria Additional criteria
     * @param string $alias SQL query table alias
     * @return QueryBuilder
     */
    protected function createSearchBy(
        string $pattern,
        QueryBuilder $qb,
        array &$criteria = [],
        string $alias = "obj"
    ): QueryBuilder {
        $this->classicLikeComparison($pattern, $qb, $alias);

        /*
         * Search in translations
         */
        $qb->leftJoin('obj.translatedFolders', 'tf');

        $criteriaFields = [];
        foreach (self::getSearchableColumnsNames($this->_em->getClassMetadata(FolderTranslation::class)) as $field) {
            $criteriaFields[$field] = '%' . strip_tags(mb_strtolower($pattern)) . '%';
        }

        foreach ($criteriaFields as $key => $value) {
            $fullKey = sprintf('LOWER(%s)', 'tf.' . $key);
            $qb->orWhere($qb->expr()->like($fullKey, $qb->expr()->literal($value)));
        }

        return $this->prepareComparisons($criteria, $qb, $alias);
    }

    /**
     * @param string $pattern
     * @param array $criteria
     * @param string $alias
     * @return int
     * @throws \Doctrine\ORM\NoResultException
     * @throws NonUniqueResultException
     */
    public function countSearchBy(string $pattern, array $criteria = [], string $alias = "obj"): int
    {
        $qb = $this->createQueryBuilder($alias);
        $qb->select($qb->expr()->countDistinct($alias));
        $qb = $this->createSearchBy($pattern, $qb, $criteria, $alias);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param Document $document
     * @param TranslationInterface|null $translation
     * @return array
     */
    public function findByDocumentAndTranslation(Document $document, TranslationInterface $translation = null): array
    {
        $qb = $this->createQueryBuilder('f');
        $qb->innerJoin('f.documents', 'd')
            ->andWhere($qb->expr()->eq('d.id', ':documentId'))
            ->setParameter(':documentId', $document->getId());

        if (null !== $translation) {
            $qb->addSelect('tf')
                ->leftJoin(
                    'f.translatedFolders',
                    'tf',
                    Join::WITH,
                    'tf.translation = :translation'
                )
                ->setParameter(':translation', $translation);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Folder|null $parent
     * @param TranslationInterface|null $translation
     * @return array
     */
    public function findByParentAndTranslation(Folder $parent = null, TranslationInterface $translation = null): array
    {
        $qb = $this->createQueryBuilder('f');
        $qb->addOrderBy('f.position', 'ASC');

        if (null === $parent) {
            $qb->andWhere($qb->expr()->isNull('f.parent'));
        } else {
            $qb->andWhere($qb->expr()->eq('f.parent', ':parent'))
                ->setParameter(':parent', $parent);
        }

        if (null !== $translation) {
            $qb->addSelect('tf')
                ->leftJoin(
                    'f.translatedFolders',
                    'tf',
                    Join::WITH,
                    'tf.translation = :translation'
                )
                ->setParameter(':translation', $translation);
        }

        return $qb->getQuery()->getResult();
    }
}
