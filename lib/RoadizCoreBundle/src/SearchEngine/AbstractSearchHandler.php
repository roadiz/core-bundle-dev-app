<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine;

use Doctrine\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Exception\SolrServerNotAvailableException;
use Solarium\Core\Client\Client;
use Solarium\Core\Query\Helper;
use Solarium\QueryType\Select\Query\Query;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

abstract class AbstractSearchHandler implements SearchHandlerInterface
{
    protected LoggerInterface $logger;
    protected int $highlightingFragmentSize = 150;

    public function __construct(
        protected readonly ClientRegistry $clientRegistry,
        protected readonly ObjectManager $em,
        LoggerInterface $searchEngineLogger,
        protected readonly EventDispatcherInterface $eventDispatcher,
    ) {
        $this->logger = $searchEngineLogger;
    }

    public function getSolr(): Client
    {
        $solr = $this->clientRegistry->getClient();
        if (null === $solr) {
            throw new SolrServerNotAvailableException();
        }

        return $solr;
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
        return new SolrSearchResults(null !== $response ? $response : [], $this->em);
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

    public function setHighlightingFragmentSize(int $highlightingFragmentSize): AbstractSearchHandler
    {
        $this->highlightingFragmentSize = $highlightingFragmentSize;

        return $this;
    }

    abstract protected function nativeSearch(
        string $q,
        array $args = [],
        int $rows = 20,
        bool $searchTags = false,
        int $page = 1,
    ): ?array;

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
        $input = preg_replace('#\\b(AND|OR|NOT)\\b#', '\\\\\\\$1', $input);

        return $input;
    }

    /**
     * @return array [$exactQuery, $fuzzyQuery, $wildcardQuery]
     */
    protected function getFormattedQuery(string $q): array
    {
        $q = trim($q);
        /**
         * Generate a fuzzy query by appending proximity to each word.
         *
         * @see https://lucene.apache.org/solr/guide/6_6/the-standard-query-parser.html#TheStandardQueryParser-FuzzySearches
         */
        $words = preg_split('#[\s,]+#', $q, -1, PREG_SPLIT_NO_EMPTY);
        if (false === $words) {
            throw new \RuntimeException('Cannot split query string.');
        }
        $fuzzyiedQuery = implode(' ', array_map(function (string $word) {
            /*
             * Do not fuzz short words: Solr crashes
             * Proximity is set to 1 by default for single-words
             */
            if (\mb_strlen($word) > 3) {
                return $this->escapeQuery($word).'~2';
            }

            return $this->escapeQuery($word);
        }, $words));
        /*
         * Only escape exact query
         */
        $exactQuery = $this->escapeQuery($q);
        /*
         * Wildcard search for allowing autocomplete
         */
        $wildcardQuery = $this->escapeQuery($q).'*~2';

        return [$exactQuery, $fuzzyiedQuery, $wildcardQuery];
    }

    /**
     * Default Solr query builder.
     *
     * Extends this method to customize your Solr queries. Eg. to boost custom fields.
     */
    protected function buildQuery(string $q, array &$args, bool $searchTags = false): string
    {
        $titleField = $this->getTitleField($args);
        $collectionField = $this->getCollectionField($args);
        $tagsField = $this->getTagsField($args);
        [$exactQuery, $fuzzyiedQuery, $wildcardQuery] = $this->getFormattedQuery($q);

        /*
         * Search in node-sources tags name…
         */
        if ($searchTags) {
            // Need to use Fuzzy search AND Exact search
            return sprintf(
                '('.$titleField.':%s)^10 ('.$titleField.':%s) ('.$titleField.':%s) ('.$collectionField.':%s)^2 ('.$collectionField.':%s) ('.$tagsField.':%s) ('.$tagsField.':%s)',
                $exactQuery,
                $fuzzyiedQuery,
                $wildcardQuery,
                $exactQuery,
                $fuzzyiedQuery,
                $exactQuery,
                $fuzzyiedQuery
            );
        } else {
            return sprintf(
                '('.$titleField.':%s)^10 ('.$titleField.':%s) ('.$titleField.':%s) ('.$collectionField.':%s)^2 ('.$collectionField.':%s)',
                $exactQuery,
                $fuzzyiedQuery,
                $wildcardQuery,
                $exactQuery,
                $fuzzyiedQuery
            );
        }
    }

    protected function buildHighlightingQuery(string $q): string
    {
        $q = trim($q);
        $words = preg_split('#[\s,]+#', $q, -1, PREG_SPLIT_NO_EMPTY);
        if (\is_array($words) && \count($words) > 1) {
            return $this->escapeQuery($q);
        }

        $q = $this->escapeQuery($q);

        return sprintf('%s~2', $q);
    }

    protected function buildQueryFields(array &$args, bool $searchTags = true): string
    {
        $titleField = $this->getTitleField($args);
        $collectionField = $this->getCollectionField($args);
        $tagsField = $this->getTagsField($args);

        if ($searchTags) {
            return $titleField.'^10 '.$collectionField.'^2 '.$tagsField.' slug_s';
        }

        return $titleField.' '.$collectionField.' slug_s';
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
