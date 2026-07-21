<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\ORM\NonUniqueResultException;
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
        EventDispatcherInterface $dispatcher,
    ) {
        parent::__construct($registry, Folder::class, $dispatcher);
    }

    /**
     * Find a folder according to the given path or create it.
     */
    public function findOrCreateByPath(string $folderPath, ?TranslationInterface $translation = null): ?Folder
    {
        $folderPath = trim($folderPath);
        $folders = explode('/', $folderPath);
        $folders = array_filter($folders);

        if (0 === count($folders)) {
            return null;
        }

        $folderName = $folders[count($folders) - 1];
        $folder = $this->findOneByFolderName($folderName);

        if (null !== $folder) {
            return $folder;
        }

        /*
         * Creation of a new folder
         * before linking it to the node
         */
        $parentFolder = null;

        if (count($folders) > 1) {
            // Call recursively to create parent folder if not exists with $folders array without last element
            $parentFolder = $this->findOrCreateByPath(implode('/', array_slice($folders, 0, -1)), $translation);
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
            $translation = $this->getEntityManager()->getRepository(Translation::class)->findDefault() ?? throw new \InvalidArgumentException('No default translation found.');
        }
        $folderTranslation = new FolderTranslation($folder, $translation);
        $folderTranslation->setName($folderName);

        $this->getEntityManager()->persist($folder);
        $this->getEntityManager()->persist($folderTranslation);
        $this->getEntityManager()->flush();

        return $folder;
    }

    /**
     * Find a folder according to the given path.
     *
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

    public function findAllChildrenFromFolder(Folder $folder, ?TranslationInterface $translation = null): array
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
     * @throws NonUniqueResultException
     */
    public function findOneByFolderName(string $folderName, ?TranslationInterface $translation = null): ?Folder
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

    public function findAllChildrenIdFromFolder(Folder $folder): array
    {
        $idsArray = $this->findChildrenIdFromFolder($folder);

        /** @var Folder $child */
        foreach ($folder->getChildren() as $child) {
            $idsArray = array_merge($idsArray, $this->findAllChildrenIdFromFolder($child));
        }

        return $idsArray;
    }

    public function findChildrenIdFromFolder(Folder $folder): array
    {
        $qb = $this->createQueryBuilder('f');
        $qb->select('f.id')
            ->where($qb->expr()->eq('f.parent', ':parent'))
            ->setParameter(':parent', $folder);

        return array_map(current(...), $qb->getQuery()->getScalarResult());
    }

    #[\Override]
    protected function createSearchBy(
        string $pattern,
        QueryBuilder $qb,
        array &$criteria = [],
        string $alias = EntityRepository::DEFAULT_ALIAS,
    ): QueryBuilder {
        $this->classicLikeComparison($pattern, $qb, $alias);

        /*
         * Search in translations
         */
        $qb->leftJoin($alias.'.translatedFolders', 'tf');

        $criteriaFields = [];
        foreach (self::getSearchableColumnsNames($this->getEntityManager()->getClassMetadata(FolderTranslation::class)) as $field) {
            $criteriaFields[$field] = '%'.strip_tags(\mb_strtolower($pattern)).'%';
        }

        foreach ($criteriaFields as $key => $value) {
            $fullKey = sprintf('LOWER(%s)', 'tf.'.$key);
            $qb->orWhere($qb->expr()->like($fullKey, $qb->expr()->literal($value)));
        }

        return $this->prepareComparisons($criteria, $qb, $alias);
    }

    public function findByDocumentAndTranslation(Document $document, ?TranslationInterface $translation = null): array
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

    public function findByParentAndTranslation(?Folder $parent = null, ?TranslationInterface $translation = null): array
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
