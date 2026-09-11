<?php

declare(strict_types=1);

namespace RZ\Roadiz\SolrBundle;

use Doctrine\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\SearchEngine\SearchHandlerInterface;
use RZ\Roadiz\CoreBundle\SearchEngine\SearchResultsInterface;
use RZ\Roadiz\SolrBundle\Event\AbstractSearchQueryEvent;
use RZ\Roadiz\SolrBundle\Exception\SolrServerNotAvailableException;
use RZ\Roadiz\SolrBundle\Exception\SolrServerNotConfiguredException;
use Solarium\Core\Client\Client;
use Solarium\Core\Query\Helper;
use Solarium\Exception\ExceptionInterface;
use Solarium\QueryType\Select\Query\Query;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

abstract class AbstractSearchHandler implements SearchHandlerInterface
{
    protected const int DEFAULT_TITLE_BOOST = 10;
    protected const int EXACT_TITLE_BOOST = 20;
    protected const int EXACT_COLLECTION_BOOST = 2;
    /**
     * Word distance tolerance for the eDisMax phrase boost (`ps` parameter).
     */
    protected const int EXACT_PHRASE_SLOP = 2;
    protected int $highlightingFragmentSize = 150;
    /**
     * Specifies the breakiterator type for dividing the document into passages.
     * Can be SEPARATOR, SENTENCE, WORD, CHARACTER, LINE, or WHOLE.
     *
     * @see https://solr.apache.org/guide/solr/latest/query-guide/highlighting.html
     */
    protected SolrHighlightingBsTypeEnum $highlightingBsType = SolrHighlightingBsTypeEnum::WORD;
    protected SolrHighlightingMethodEnum $highlightingMethod = SolrHighlightingMethodEnum::UNIFIED;

    public function __construct(
        protected readonly ClientRegistryInterface $clientRegistry,
        protected readonly ObjectManager $em,
        protected readonly LoggerInterface $searchEngineLogger,
        protected readonly EventDispatcherInterface $eventDispatcher,
        protected readonly int $fuzzyProximity,
        protected readonly int $fuzzyMinTermLength,
    ) {
    }

    public function getSolr(): Client
    {
        try {
            $solr = $this->clientRegistry->getClient();
            if (null === $solr) {
                throw new SolrServerNotConfiguredException();
            }

            return $solr;
        } catch (ExceptionInterface $e) {
            throw new SolrServerNotAvailableException(previous: $e);
        }
    }

    /**
     * Search on Solr with pre-filled argument for highlighting.
     *
     * * $q is the search criteria.
     * * $args is an array with solr query argument.
     * The common argument can be found [here](https://cwiki.apache.org/confluence/display/solr/Common+Query+Parameters)
     *  and for highlighting argument is [here](https://cwiki.apache.org/confluence/display/solr/Standard+Highlighter).
     *
     * @param bool $searchTags Search in tags/folders too, even if a node don’t match
     *
     * @return SearchResultsInterface return a SearchResultsInterface iterable object
     */
    #[\Override]
    public function searchWithHighlight(
        string $q,
        array $args = [],
        int $rows = 20,
        bool $searchTags = false,
        int $page = 1,
    ): SearchResultsInterface {
        $args = $this->argFqProcess($args);
        $args['fq'][] = 'document_type_s:'.$this->getDocumentType();
        $args['hl.q'] = $this->buildHighlightingQuery($q);
        $args = array_merge($this->getHighlightingOptions($args), $args);
        $response = $this->nativeSearch($q, $args, $rows, $searchTags, $page);

        return $this->createSearchResultsFromResponse($response);
    }

    protected function createSearchResultsFromResponse(?array $response): SolrSearchResults
    {
        return new SolrSearchResults($response ?? [], $this->em);
    }

    abstract protected function argFqProcess(array &$args): array;

    abstract protected function getDocumentType(): string;

    protected function getHighlightingOptions(array &$args = []): array
    {
        $tmp = [];
        $tmp['hl'] = true;
        $tmp['hl.fl'] = $this->getTitleField($args).' '.$this->getCollectionField($args);
        $tmp['hl.fragsize'] = $this->getHighlightingFragmentSize();
        $tmp['hl.simple.pre'] = '<span class="solr-highlight">';
        $tmp['hl.simple.post'] = '</span>';
        $tmp['hl.method'] = $this->getHighlightingMethod()->value;
        $tmp['hl.bs.type'] = $this->getHighlightingBsType()->value;

        if (
            SolrHighlightingMethodEnum::ORIGINAL !== $this->getHighlightingMethod()
            && isset($args['locale'])
            && is_string($args['locale'])
        ) {
            $tmp['hl.bs.language'] = \Locale::getPrimaryLanguage($args['locale']);
        }

        return $tmp;
    }

    protected function getCollectionField(array &$args): string
    {
        /*
         * Use collection_txt_LOCALE when search
         * is filtered by translation.
         */
        if (isset($args['locale']) && is_string($args['locale'])) {
            return 'collection_txt_'.\Locale::getPrimaryLanguage($args['locale']);
        }
        if (isset($args['translation']) && $args['translation'] instanceof Translation) {
            return 'collection_txt_'.\Locale::getPrimaryLanguage($args['translation']->getLocale());
        }

        return 'collection_txt';
    }

    public function getHighlightingFragmentSize(): int
    {
        return $this->highlightingFragmentSize;
    }

    #[\Override]
    public function setHighlightingFragmentSize(int $highlightingFragmentSize): AbstractSearchHandler
    {
        $this->highlightingFragmentSize = $highlightingFragmentSize;

        return $this;
    }

    public function getHighlightingBsType(): SolrHighlightingBsTypeEnum
    {
        return $this->highlightingBsType;
    }

    public function setHighlightingBsType(SolrHighlightingBsTypeEnum $highlightingBsType): AbstractSearchHandler
    {
        $this->highlightingBsType = $highlightingBsType;

        return $this;
    }

    public function getHighlightingMethod(): SolrHighlightingMethodEnum
    {
        return $this->highlightingMethod;
    }

    public function setHighlightingMethod(SolrHighlightingMethodEnum $highlightingMethod): AbstractSearchHandler
    {
        $this->highlightingMethod = $highlightingMethod;

        return $this;
    }

    /**
     * Fields to fetch back from Solr: just enough for Doctrine to hydrate the
     * real entities.
     *
     * @return string[]
     */
    abstract protected function getResultFields(): array;

    /**
     * @param array<string, mixed> $args
     */
    abstract protected function createSearchQueryEvent(Query $query, array $args): AbstractSearchQueryEvent;

    /**
     * Multiplicative boost function applied to the whole query (eDisMax `boost`
     * parameter). Null disables it.
     */
    protected function getBoostFunction(): ?string
    {
        return null;
    }

    protected function nativeSearch(
        string $q,
        array $args = [],
        int $rows = 20,
        bool $searchTags = false,
        int $page = 1,
    ): ?array {
        if ('' === trim($q)) {
            return null;
        }
        $queryTxt = $this->buildQuery($q, $args, $searchTags);
        if ('' === $queryTxt) {
            return null;
        }
        $query = $this->createSolrQuery($args, $rows, $page);
        $query->setQuery($queryTxt);
        $this->configureQueryParser($query, $args, $searchTags);
        $query->setFields($this->getResultFields());

        $this->searchEngineLogger->debug(sprintf('[Solr] Request %s search…', $this->getDocumentType()), [
            'query' => $queryTxt,
            'fq' => $args['fq'] ?? [],
            'params' => $query->getParams(),
        ]);

        $event = $this->eventDispatcher->dispatch($this->createSearchQueryEvent($query, $args));
        $query = $event->getQuery();

        return $this->getSolr()->execute($query)->getData();
    }

    /**
     * Set up the eDisMax query parser: which fields are searched and how they
     * are weighted lives here, not in the query string itself.
     */
    protected function configureQueryParser(Query $query, array &$args, bool $searchTags = false): void
    {
        $edisMax = $query->getEDisMax();
        $edisMax->setQueryFields($this->buildQueryFields($args, $searchTags));
        $edisMax->setPhraseFields($this->buildPhraseFields($args));
        $edisMax->setPhraseSlop(static::EXACT_PHRASE_SLOP);
        $edisMax->setMinimumMatch($this->getMinimumMatch());

        $boostFunction = $this->getBoostFunction();
        if (null !== $boostFunction) {
            $edisMax->setBoostFunctionsMult($boostFunction);
        }
    }

    /**
     * eDisMax `mm`: how many of the query words a document must match. Defaults
     * to every one of them, override to loosen it.
     */
    protected function getMinimumMatch(): string
    {
        return '100%';
    }

    /**
     * ## Search on Solr.
     *
     * * $q is the search criteria.
     * * $args is a array with solr query argument.
     * The common argument can be found [here](https://cwiki.apache.org/confluence/display/solr/Common+Query+Parameters)
     *  and for highlighting argument is [here](https://cwiki.apache.org/confluence/display/solr/Standard+Highlighter).
     *
     * You can use shortcuts in $args array to filter:
     *
     * ### For node-sources:
     *
     * * status (int)
     * * visible (bool)
     * * nodeType (RZ\Roadiz\CoreBundle\Entity\NodeType or string or array)
     * * tags (RZ\Roadiz\CoreBundle\Entity\Tag or array of Tag)
     * * translation (RZ\Roadiz\CoreBundle\Entity\Translation)
     *
     * For other filters, use $args['fq'][] array, eg.
     *
     *     $args["fq"][] = "title:My title";
     *
     * this explicitly filter by title.
     *
     * @param int  $rows       Results per page
     * @param bool $searchTags Search in tags/folders too, even if a node don’t match
     * @param int  $page       Retrieve a specific page
     *
     * @return SearchResultsInterface Return an array of doctrine Entities (Document, NodesSources)
     */
    #[\Override]
    public function search(
        string $q,
        array $args = [],
        int $rows = 20,
        bool $searchTags = false,
        int $page = 1,
    ): SearchResultsInterface {
        $args = $this->argFqProcess($args);
        $args['fq'][] = 'document_type_s:'.$this->getDocumentType();
        $tmp = [];
        $args = array_merge($tmp, $args);

        $response = $this->nativeSearch($q, $args, $rows, $searchTags, $page);

        return $this->createSearchResultsFromResponse($response);
    }

    public function escapeQuery(string $input): string
    {
        $qHelper = new Helper();
        $input = $qHelper->filterControlCharacters($input);
        $input = $qHelper->escapeTerm($input);

        // Solarium does not escape Lucene reserved words
        // https://stackoverflow.com/questions/10337908/how-to-properly-escape-or-and-and-in-lucene-query
        return preg_replace('#\\b(AND|OR|NOT)\\b#', '\\\\\\\$1', $input) ?? $input;
    }

    /**
     * Escape a free-string value as a quoted Lucene phrase for use in a filter
     * query (`field:<escaped>`). Surrounds the value with double quotes and
     * backslash-escapes any `"`/`\`, preventing injection of boolean operators,
     * local params or additional `field:value` clauses.
     *
     * Only use this for free strings, never for range/numeric/structural syntax.
     */
    protected function escapePhrase(string $input): string
    {
        $qHelper = new Helper();
        $input = $qHelper->filterControlCharacters($input);

        return $qHelper->escapePhrase($input);
    }

    /**
     * @return string[]
     */
    protected function splitQuery(string $q): array
    {
        $words = preg_split('#[\s,]+#', trim($q), -1, PREG_SPLIT_NO_EMPTY);
        if (false === $words) {
            throw new \RuntimeException('Cannot split query string.');
        }

        return $words;
    }

    /**
     * Build the three query forms the standard Lucene parser needed: a quoted
     * PhraseQuery, an AND-joined fuzzy group and a wildcard variant.
     *
     * @return array{0: string, 1: string, 2: string} [$exactQuery, $fuzzyQuery, $wildcardQuery]
     *
     * @deprecated since 2.7, eDisMax builds these clauses itself. Declare the
     *             searched fields through buildQueryFields()/buildPhraseFields()
     *             instead of composing a field-scoped query string by hand.
     */
    protected function getFormattedQuery(string $q): array
    {
        $q = trim($q);
        $fuzzyiedQuery = '('.implode(' AND ', array_map(function (string $word) {
            if ($this->shouldFuzzify($word)) {
                return $this->escapeQuery($word).$this->getFuzzySuffix();
            }

            return $this->escapeQuery($word);
        }, $this->splitQuery($q))).')';
        $exactQuery = $this->escapePhrase($q).'~'.static::EXACT_PHRASE_SLOP;
        $wildcardQuery = $this->escapeQuery($q).'*';
        if ($this->shouldFuzzify($q)) {
            $wildcardQuery .= $this->getFuzzySuffix();
        }

        return [$exactQuery, $fuzzyiedQuery, $wildcardQuery];
    }

    /**
     * Default Solr query builder.
     *
     * Under eDisMax the query string carries the terms only: fields, weights and
     * phrase boosting are declared through `qf`/`pf` in configureQueryParser().
     * Extend this method to customize how user words are turned into terms.
     *
     * @see https://lucene.apache.org/solr/guide/6_6/the-standard-query-parser.html#TheStandardQueryParser-FuzzySearches
     */
    protected function buildQuery(string $q, array &$args, bool $searchTags = false): string
    {
        return implode(' ', array_map(function (string $word) {
            /*
             * Do not fuzz short words: Solr crashes
             * Proximity is configurable and can be disabled.
             */
            if ($this->shouldFuzzify($word)) {
                return $this->escapeQuery($word).$this->getFuzzySuffix();
            }

            return $this->escapeQuery($word);
        }, $this->splitQuery($q)));
    }

    protected function buildHighlightingQuery(string $q): string
    {
        return $this->escapeQuery(trim($q));
    }

    final protected function shouldFuzzify(string $word): bool
    {
        return $this->fuzzyProximity > 0
            && \mb_strlen($word) >= $this->fuzzyMinTermLength;
    }

    final protected function getFuzzySuffix(): string
    {
        return '~'.$this->fuzzyProximity;
    }

    /**
     * eDisMax `qf`: searched fields and their weights.
     *
     * Only declare fields that this document type actually indexes: a field the
     * schema does not know at all (directly or through a dynamic field) makes
     * Solr reject the whole request, and one that is merely never populated
     * just dilutes the query for nothing.
     */
    protected function buildQueryFields(array &$args, bool $searchTags = true): string
    {
        $fields = [
            sprintf('%s^%d', $this->getTitleField($args), static::DEFAULT_TITLE_BOOST),
            sprintf('%s^%d', $this->getCollectionField($args), static::EXACT_COLLECTION_BOOST),
        ];

        if ($searchTags) {
            $fields[] = $this->getTagsField($args);
        }

        return implode(' ', $fields);
    }

    /**
     * eDisMax `pf`: fields where matching the words as a phrase earns a boost.
     * This is what replaces the hand-built exact PhraseQuery.
     */
    protected function buildPhraseFields(array &$args): string
    {
        return sprintf(
            '%s^%d %s^%d',
            $this->getTitleField($args),
            static::EXACT_TITLE_BOOST,
            $this->getCollectionField($args),
            static::EXACT_COLLECTION_BOOST
        );
    }

    protected function isQuerySingleWord(string $q): bool
    {
        return 1 !== preg_match('#[\s\-\'\"\–\—\’\”\‘\“\/\+\.\,]#', $q);
    }

    protected function formatDateTimeToUTC(\DateTimeInterface $dateTime): string
    {
        return gmdate('Y-m-d\TH:i:s\Z', $dateTime->getTimestamp());
    }

    protected function getTitleField(array &$args): string
    {
        /*
         * Use title_txt_LOCALE when search
         * is filtered by translation.
         */
        if (isset($args['locale']) && is_string($args['locale'])) {
            return 'title_txt_'.\Locale::getPrimaryLanguage($args['locale']);
        }
        if (isset($args['translation']) && $args['translation'] instanceof Translation) {
            return 'title_txt_'.\Locale::getPrimaryLanguage($args['translation']->getLocale());
        }

        return 'title';
    }

    protected function getTagsField(array &$args): string
    {
        /*
         * Use tags_txt_LOCALE when search
         * is filtered by translation.
         */
        if (isset($args['locale']) && is_string($args['locale'])) {
            return 'tags_txt_'.\Locale::getPrimaryLanguage($args['locale']);
        }
        if (isset($args['translation']) && $args['translation'] instanceof Translation) {
            return 'tags_txt_'.\Locale::getPrimaryLanguage($args['translation']->getLocale());
        }

        return 'tags_txt';
    }

    /**
     * Create Solr Select query. Override it to add DisMax fields and rules.
     *
     * @param array<string, mixed> $args
     */
    protected function createSolrQuery(array &$args = [], int $rows = 20, int $page = 1): Query
    {
        $query = $this->getSolr()->createSelect();
        foreach ($args as $key => $value) {
            if (is_array($value)) {
                $value = array_unique($value);
                foreach ($value as $k => $v) {
                    $query->addFilterQuery([
                        'key' => 'fq_'.$key.'_'.$k,
                        'query' => $v,
                    ]);
                }
            } elseif (is_scalar($value)) {
                $query->addParam($key, $value);
            }
        }
        /*
         * Add start if not first page.
         */
        if ($page > 1) {
            $query->setStart(($page - 1) * $rows);
        }
        $query->addSort('score', $query::SORT_DESC);
        $query->setRows($rows);

        return $query;
    }
}
