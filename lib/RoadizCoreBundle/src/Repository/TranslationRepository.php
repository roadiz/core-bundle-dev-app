<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\Folder;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @method Translation|null findOneByName(string $name)
 * @method Translation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Translation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Translation[]    findAll()
 * @method Translation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @extends EntityRepository<Translation>
 */
final class TranslationRepository extends EntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        EventDispatcherInterface $dispatcher
    ) {
        parent::__construct($registry, Translation::class, $dispatcher);
    }

    /**
     * Get single default translation.
     *
     * @return TranslationInterface|null
     * @throws NonUniqueResultException
     */
    public function findDefault(): ?TranslationInterface
    {
        $qb = $this->createQueryBuilder('t');
        $qb->andWhere($qb->expr()->eq('t.available', ':available'))
            ->andWhere($qb->expr()->eq('t.defaultTranslation', ':defaultTranslation'))
            ->setParameter(':available', true)
            ->setParameter(':defaultTranslation', true)
            ->setMaxResults(1)
            ->setCacheable(true);

        $query = $qb->getQuery();
        $query->enableResultCache(1800, 'RZTranslationDefault');

        return $query->getOneOrNullResult();
    }

    /**
     * Get all available translations.
     *
     * @return TranslationInterface[]
     */
    public function findAllAvailable(): array
    {
        $qb = $this->createQueryBuilder('t');
        $qb->andWhere($qb->expr()->eq('t.available', ':available'))
            // Default translation should be first
            ->addOrderBy('t.defaultTranslation', 'DESC')
            ->setParameter(':available', true)
            ->setCacheable(true);

        $query = $qb->getQuery();
        $query->enableResultCache(1800, 'RZTranslationAllAvailable');

        return $query->getResult();
    }

    /**
     * @param string $locale
     *
     * @return bool
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function exists(string $locale): bool
    {
        $qb = $this->createQueryBuilder('t');
        $qb->select($qb->expr()->countDistinct('t.locale'))
            ->andWhere($qb->expr()->eq('t.locale', ':locale'))
            ->setParameter(':locale', $locale)
            ->setCacheable(true);

        $query = $qb->getQuery();
        $query->enableResultCache(120, 'RZTranslationExists-' . $locale);

        return (bool) $query->getSingleScalarResult();
    }

    /**
     * Get all available locales.
     *
     * @return array
     */
    public function getAvailableLocales(): array
    {
        $qb = $this->createQueryBuilder('t');
        $qb->select('t.locale')
            ->andWhere($qb->expr()->eq('t.available', ':available'))
            // Default translation should be first
            ->addOrderBy('t.defaultTranslation', 'DESC')
            ->setParameter(':available', true)
            ->setCacheable(true);

        $query = $qb->getQuery();
        $query->enableResultCache(120, 'RZTranslationGetAvailableLocales');

        return array_map('current', $query->getScalarResult());
    }

    /**
     * Get all locales.
     *
     * @return array
     */
    public function getAllLocales(): array
    {
        $qb = $this->createQueryBuilder('t');
        $qb->select('t.locale')
            // Default translation should be first
            ->addOrderBy('t.defaultTranslation', 'DESC')
            ->setCacheable(true);

        $query = $qb->getQuery();
        $query->enableResultCache(120, 'RZTranslationGetAllLocales');

        return array_map('current', $query->getScalarResult());
    }

    /**
     * Get all available locales.
     *
     * @return array
     */
    public function getAvailableOverrideLocales(): array
    {
        $qb = $this->createQueryBuilder('t');
        $qb->select('t.overrideLocale')
            ->andWhere($qb->expr()->isNotNull('t.overrideLocale'))
            ->andWhere($qb->expr()->neq('t.overrideLocale', ':overrideLocale'))
            ->andWhere($qb->expr()->eq('t.available', ':available'))
            // Default translation should be first
            ->addOrderBy('t.defaultTranslation', 'DESC')
            ->setParameter(':available', true)
            ->setParameter(':overrideLocale', '')
            ->setCacheable(true);

        $query = $qb->getQuery();
        $query->enableResultCache(120, 'RZTranslationGetAvailableOverrideLocales');

        return array_map('current', $query->getScalarResult());
    }

    /**
     * Get all available locales.
     *
     * @return array
     */
    public function getAllOverrideLocales(): array
    {
        $qb = $this->createQueryBuilder('t');
        $qb->select('t.overrideLocale')
            ->andWhere($qb->expr()->isNotNull('t.overrideLocale'))
            ->andWhere($qb->expr()->neq('t.overrideLocale', ':overrideLocale'))
            // Default translation should be first
            ->addOrderBy('t.defaultTranslation', 'DESC')
            ->setParameter(':overrideLocale', '')
            ->setCacheable(true);

        $query = $qb->getQuery();
        $query->enableResultCache(120, 'RZTranslationGetAllOverrideLocales');

        return array_map('current', $query->getScalarResult());
    }

    /**
     * Get all available translations by locale.
     *
     * @param string $locale
     *
     * @return TranslationInterface[]
     */
    public function findByLocaleAndAvailable(string $locale): array
    {
        $qb = $this->createQueryBuilder(self::TRANSLATION_ALIAS);
        $qb->andWhere($qb->expr()->eq(self::TRANSLATION_ALIAS . '.available', ':available'))
            ->andWhere($qb->expr()->eq(self::TRANSLATION_ALIAS . '.locale', ':locale'))
            ->setParameter('available', true)
            ->setParameter('locale', $locale)
            ->setCacheable(true);

        $query = $qb->getQuery();
        $query->enableResultCache(
            120,
            'RZTranslationAllByLocaleAndAvailable-' . $locale
        );

        return $query->getResult();
    }

    /**
     * Get all available translations by overrideLocale.
     *
     * @param string $overrideLocale
     * @return TranslationInterface[]
     */
    public function findByOverrideLocaleAndAvailable(string $overrideLocale): array
    {
        $qb = $this->createQueryBuilder(self::TRANSLATION_ALIAS);
        $qb->andWhere($qb->expr()->eq(self::TRANSLATION_ALIAS . '.available', ':available'))
            ->andWhere($qb->expr()->eq(self::TRANSLATION_ALIAS . '.overrideLocale', ':overrideLocale'))
            ->setParameter('available', true)
            ->setParameter('overrideLocale', $overrideLocale)
            ->setCacheable(true);

        $query = $qb->getQuery();
        $query->enableResultCache(
            120,
            'RZTranslationAllByOverrideAndAvailable-' . $overrideLocale
        );

        return $query->getResult();
    }

    /**
     * Get one translation by locale or override locale.
     *
     * @param string $locale
     * @param string $alias
     *
     * @return TranslationInterface|null
     * @throws NonUniqueResultException
     */
    public function findOneByLocaleOrOverrideLocale(
        string $locale,
        string $alias = TranslationRepository::TRANSLATION_ALIAS
    ): ?TranslationInterface {
        $qb = $this->createQueryBuilder($alias);
        $qb->andWhere($qb->expr()->orX(
            $qb->expr()->eq($alias . '.locale', ':locale'),
            $qb->expr()->eq($alias . '.overrideLocale', ':locale')
        ))
            ->setParameter('locale', $locale)
            ->setMaxResults(1)
            ->setCacheable(true);

        $query = $qb->getQuery();
        $query->enableResultCache(120, 'findOneByLocaleOrOverrideLocale_' . $locale);

        return $query->getOneOrNullResult();
    }

    /**
     * Get one available translation by locale or override locale.
     *
     * @param string $locale
     *
     * @return TranslationInterface|null
     * @throws NonUniqueResultException
     */
    public function findOneAvailableByLocaleOrOverrideLocale(string $locale): ?TranslationInterface
    {
        $qb = $this->createQueryBuilder(self::TRANSLATION_ALIAS);
        $qb->andWhere($qb->expr()->orX(
            $qb->expr()->eq(self::TRANSLATION_ALIAS . '.locale', ':locale'),
            $qb->expr()->eq(self::TRANSLATION_ALIAS . '.overrideLocale', ':locale')
        ))
            ->andWhere($qb->expr()->eq(self::TRANSLATION_ALIAS . '.available', ':available'))
            ->setParameter('available', true)
            ->setParameter('locale', $locale)
            ->setMaxResults(1)
            ->setCacheable(true);

        $query = $qb->getQuery();
        $query->enableResultCache(120, 'findOneAvailableByLocaleOrOverrideLocale_' . $locale);

        return $query->getOneOrNullResult();
    }

    /**
     * Get one available translation by locale.
     *
     * @param string $locale
     *
     * @return TranslationInterface|null
     * @throws NonUniqueResultException
     */
    public function findOneByLocaleAndAvailable(string $locale): ?TranslationInterface
    {
        $qb = $this->createQueryBuilder(self::TRANSLATION_ALIAS);
        $qb->andWhere($qb->expr()->eq(self::TRANSLATION_ALIAS . '.available', ':available'))
            ->andWhere($qb->expr()->eq(self::TRANSLATION_ALIAS . '.locale', ':locale'))
            ->setParameter('available', true)
            ->setParameter('locale', $locale)
            ->setMaxResults(1)
            ->setCacheable(true);

        $query = $qb->getQuery();
        $query->enableResultCache(120, 'RZTranslationOneByLocaleAndAvailable-' . $locale);

        return $query->getOneOrNullResult();
    }

    /**
     * Get one available translation by overrideLocale.
     *
     * @param string $overrideLocale
     *
     * @return TranslationInterface|null
     * @throws NonUniqueResultException
     */
    public function findOneByOverrideLocaleAndAvailable(string $overrideLocale): ?TranslationInterface
    {
        $qb = $this->createQueryBuilder(self::TRANSLATION_ALIAS);
        $qb->andWhere($qb->expr()->eq(self::TRANSLATION_ALIAS . '.available', ':available'))
            ->andWhere($qb->expr()->eq(self::TRANSLATION_ALIAS . '.overrideLocale', ':overrideLocale'))
            ->setParameter('available', true)
            ->setParameter('overrideLocale', $overrideLocale)
            ->setMaxResults(1)
            ->setCacheable(true);

        $query = $qb->getQuery();
        $query->enableResultCache(
            120,
            'RZTranslationOneByOverrideAndAvailable-' . $overrideLocale
        );

        return $query->getOneOrNullResult();
    }

    /**
     * @param Node $node
     * @return TranslationInterface[]
     */
    public function findAvailableTranslationsForNode(Node $node): array
    {
        $qb = $this->createQueryBuilder(self::TRANSLATION_ALIAS);
        $qb->innerJoin('t.nodeSources', self::NODESSOURCES_ALIAS)
            ->andWhere($qb->expr()->eq(self::NODESSOURCES_ALIAS . '.node', ':node'))
            ->addOrderBy('t.defaultTranslation', 'DESC')
            ->addOrderBy('t.locale', 'ASC')
            ->setParameter('node', $node)
            ->setCacheable(true);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Tag $tag
     * @return TranslationInterface[]
     */
    public function findAvailableTranslationsForTag(Tag $tag): array
    {
        $qb = $this->createQueryBuilder(self::TRANSLATION_ALIAS);
        $qb->innerJoin('t.tagTranslations', 'tt')
            ->andWhere($qb->expr()->eq('tt.tag', ':tag'))
            ->addOrderBy('t.defaultTranslation', 'DESC')
            ->addOrderBy('t.locale', 'ASC')
            ->setParameter('tag', $tag);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Folder $folder
     * @return TranslationInterface[]
     */
    public function findAvailableTranslationsForFolder(Folder $folder): array
    {
        $qb = $this->createQueryBuilder(self::TRANSLATION_ALIAS);
        $qb->innerJoin('t.folderTranslations', 'ft')
            ->andWhere($qb->expr()->eq('ft.folder', ':folder'))
            ->addOrderBy('t.defaultTranslation', 'DESC')
            ->addOrderBy('t.locale', 'ASC')
            ->setParameter('folder', $folder);

        return $qb->getQuery()->getResult();
    }

    /**
     * Find available node translations which are available too.
     *
     * @param Node $node
     * @return TranslationInterface[]
     */
    public function findStrictlyAvailableTranslationsForNode(Node $node): array
    {
        $qb = $this->createQueryBuilder(self::TRANSLATION_ALIAS);
        $qb->innerJoin('t.nodeSources', self::NODESSOURCES_ALIAS)
            ->andWhere($qb->expr()->eq(self::NODESSOURCES_ALIAS . '.node', ':node'))
            ->andWhere($qb->expr()->eq(self::TRANSLATION_ALIAS . '.available', ':available'))
            ->addOrderBy('t.defaultTranslation', 'DESC')
            ->addOrderBy('t.locale', 'ASC')
            ->setParameter('node', $node)
            ->setParameter('available', true)
            ->setCacheable(true);

        return $qb->getQuery()->getResult();
    }


    /**
     * @param Node $node
     * @return TranslationInterface[]
     */
    public function findUnavailableTranslationsForNode(Node $node): array
    {
        $qb = $this->createQueryBuilder(self::TRANSLATION_ALIAS);
        $qb->andWhere($qb->expr()->notIn('t.id', ':translationsId'))
            ->setParameter('translationsId', $this->findAvailableTranslationIdForNode($node))
            ->setCacheable(true);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Node $node
     * @return array
     */
    public function findAvailableTranslationIdForNode(Node $node): array
    {
        $qb = $this->createQueryBuilder(self::TRANSLATION_ALIAS);
        $qb->select(self::TRANSLATION_ALIAS . '.id')
            ->innerJoin('t.nodeSources', self::NODESSOURCES_ALIAS)
            ->andWhere($qb->expr()->eq(self::NODESSOURCES_ALIAS . '.node', ':node'))
            ->addOrderBy('t.defaultTranslation', 'DESC')
            ->addOrderBy('t.locale', 'ASC')
            ->setParameter('node', $node)
            ->setCacheable(true);

        return array_map('current', $qb->getQuery()->getScalarResult());
    }

    /**
     * @param Node $node
     * @return array
     */
    public function findUnavailableTranslationIdForNode(Node $node): array
    {
        $qb = $this->createQueryBuilder(self::TRANSLATION_ALIAS);
        $qb->select(self::TRANSLATION_ALIAS . '.id')
            ->andWhere($qb->expr()->notIn('t.id', ':translationsId'))
            ->setParameter('translationsId', $this->findAvailableTranslationIdForNode($node))
            ->setCacheable(true);

        return array_map('current', $qb->getQuery()->getScalarResult());
    }
}
