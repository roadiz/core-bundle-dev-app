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

abstract class AbstractSearchHandler implements SearchHandlerInterface
{
    private ClientRegistry $clientRegistry;
    protected ObjectManager $em;
    protected LoggerInterface $logger;
    protected int $highlightingFragmentSize = 150;

    public function __construct(
        ClientRegistry $clientRegistry,
        ObjectManager $em,
        LoggerInterface $searchEngineLogger
    ) {
        $this->clientRegistry = $clientRegistry;
        $this->em = $em;
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
     * Search on Solr with pre-filled argument for highlighting
     *
     * * $q is the search criteria.
     * * $args is an array with solr query argument.
     * The common argument can be found [here](https://cwiki.apache.org/confluence/display/solr/Common+Query+Parameters)
     *  and for highlighting argument is [here](https://cwiki.apache.org/confluence/display/solr/Standard+Highlighter).
     *
     * @param string $q
     * @param array $args
     * @param int $rows
     * @param bool $searchTags Search in tags/folders too, even if a node don’t match
     * @param int $proximity Proximity matching: Lucene supports finding words are a within a specific distance away.
     * @param int $page
     *
     * @return SearchResultsInterface Return a SearchResultsInterface iterable object.
     */
    public function searchWithHighlight(
        string $q,
        array $args = [],
        int $rows = 20,
        bool $searchTags = false,
        int $proximity = 1,
        int $page = 1
    ): SearchResultsInterface {
        $args = $this->argFqProcess($args);
        $args["fq"][] = "document_type_s:" . $this->getDocumentType();
        $args = array_merge($this->getHighlightingOptions($args), $args);
        $response = $this->nativeSearch($q, $args, $rows, $searchTags, $proximity, $page);
        return $this->createSearchResultsFromResponse($response);
    }

    protected function createSearchResultsFromResponse(?array $response): SolrSearchResults
    {
        return new SolrSearchResults(null !== $response ? $response : [], $this->em);
    }

    /**
     * @param array $args
     * @return array
     */
    abstract protected function argFqProcess(array &$args): array;

    /**
     * @return string
     */
    abstract protected function getDocumentType(): string;

    /**
     * @param array $args
     * @return array
     */
    protected function getHighlightingOptions(array &$args = []): array
    {
        $tmp = [];
        $tmp["hl"] = true;
        $tmp["hl.fl"] = $this->getCollectionField($args);
        $tmp["hl.fragsize"] = $this->getHighlightingFragmentSize();
        $tmp["hl.simple.pre"] = '<span class="solr-highlight">';
        $tmp["hl.simple.post"] = '</span>';

        return $tmp;
    }

    /**
     * @param array $args
     *
     * @return string
     */
    protected function getCollectionField(array &$args): string
    {
        /*
         * Use collection_txt_LOCALE when search
         * is filtered by translation.
         */
        if (isset($args['locale']) && is_string($args['locale'])) {
            return 'collection_txt_' . \Locale::getPrimaryLanguage($args['locale']);
        }
        if (isset($args['translation']) && $args['translation'] instanceof Translation) {
            return 'collection_txt_' . \Locale::getPrimaryLanguage($args['translation']->getLocale());
        }
        return 'collection_txt';
    }

    /**
     * @return int
     */
    public function getHighlightingFragmentSize(): int
    {
        return $this->highlightingFragmentSize;
    }

    /**
     * @param int $highlightingFragmentSize
     *
     * @return AbstractSearchHandler
     */
    public function setHighlightingFragmentSize(int $highlightingFragmentSize): AbstractSearchHandler
    {
        $this->highlightingFragmentSize = $highlightingFragmentSize;

        return $this;
    }

    /**
     * @param string $q
     * @param array $args
     * @param int $rows
     * @param bool $searchTags
     * @param int $proximity Proximity matching: Lucene supports finding words are a within a specific distance away.
     * @param int $page
     *
     * @return array|null
     */
    abstract protected function nativeSearch(
        string $q,
        array $args = [],
        int $rows = 20,
        bool $searchTags = false,
        int $proximity = 1,
        int $page = 1
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
     * * visible (boolean)
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
     *
     * @param string $q
     * @param array $args
     * @param int $rows Results per page
     * @param boolean $searchTags Search in tags/folders too, even if a node don’t match
     * @param int $proximity Proximity matching: Lucene supports finding words are a within a specific distance away. Default 10000000
     * @param int $page Retrieve a specific page
     *
     * @return SearchResultsInterface Return an array of doctrine Entities (Document, NodesSources)
     */
    public function search(
        string $q,
        array $args = [],
        int $rows = 20,
        bool $searchTags = false,
        int $proximity = 1,
        int $page = 1
    ): SearchResultsInterface {
        $args = $this->argFqProcess($args);
        $args["fq"][] = "document_type_s:" . $this->getDocumentType();
        $tmp = [];
        $args = array_merge($tmp, $args);

        $response = $this->nativeSearch($q, $args, $rows, $searchTags, $proximity, $page);
        return $this->createSearchResultsFromResponse($response);
    }

    /**
     * @param string $input
     * @return string
     */
    public function escapeQuery(string $input): string
    {
        $qHelper = new Helper();
        $input = $qHelper->filterControlCharacters($input);
        $input = $qHelper->escapeTerm($input);
        // Solarium does not escape Lucene reserved words
        // https://stackoverflow.com/questions/10337908/how-to-properly-escape-or-and-and-in-lucene-query
        $input = preg_replace("#\\b(AND|OR|NOT)\\b#", "\\\\\\\\$1", $input);

        return $input;
    }

    /**
     * @param string $q
     * @param int $proximity
     * @return array [$exactQuery, $fuzzyQuery, $wildcardQuery]
     */
    protected function getFormattedQuery(string $q, int $proximity = 1): array
    {
        $q = trim($q);
        /**
         * Generate a fuzzy query by appending proximity to each word
         * @see https://lucene.apache.org/solr/guide/6_6/the-standard-query-parser.html#TheStandardQueryParser-FuzzySearches
         */
        $words = preg_split('#[\s,]+#', $q, -1, PREG_SPLIT_NO_EMPTY);
        $fuzzyiedQuery = implode(' ', array_map(function (string $word) use ($proximity) {
            /*
             * Do not fuzz short words: Solr crashes
             * Proximity is set to 1 by default for single-words
             */
            if (strlen($word) > 3) {
                return $this->escapeQuery($word) . '~' . $proximity;
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
        $wildcardQuery = $this->escapeQuery($q) . '*~' . $proximity;

        return [$exactQuery, $fuzzyiedQuery, $wildcardQuery];
    }

    /**
     * Default Solr query builder.
     *
     * Extends this method to customize your Solr queries. Eg. to boost custom fields.
     *
     * @param string $q
     * @param array $args
     * @param bool $searchTags
     * @param int $proximity
     * @return string
     */
    protected function buildQuery(string $q, array &$args, bool $searchTags = false, int $proximity = 1): string
    {
        $titleField = $this->getTitleField($args);
        $collectionField = $this->getCollectionField($args);
        $tagsField = $this->getTagsField($args);
        [$exactQuery, $fuzzyiedQuery, $wildcardQuery] = $this->getFormattedQuery($q, $proximity);

        /*
         * Search in node-sources tags name…
         */
        if ($searchTags) {
            // Need to use Fuzzy search AND Exact search
            return sprintf(
                '(' . $titleField . ':%s)^10 (' . $titleField . ':%s) (' . $titleField . ':%s) (' . $collectionField . ':%s)^2 (' . $collectionField . ':%s) (' . $tagsField . ':%s) (' . $tagsField . ':%s)',
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
                '(' . $titleField . ':%s)^10 (' . $titleField . ':%s) (' . $titleField . ':%s) (' . $collectionField . ':%s)^2 (' . $collectionField . ':%s)',
                $exactQuery,
                $fuzzyiedQuery,
                $wildcardQuery,
                $exactQuery,
                $fuzzyiedQuery
            );
        }
    }

    /**
     * @param string $q
     *
     * @return bool
     */
    protected function isQuerySingleWord(string $q): bool
    {
        return preg_match('#[\s\-\'\"\–\—\’\”\‘\“\/\+\.\,]#', $q) !== 1;
    }

    protected function formatDateTimeToUTC(\DateTimeInterface $dateTime): string
    {
        return gmdate('Y-m-d\TH:i:s\Z', $dateTime->getTimestamp());
    }

    /**
     * @param array $args
     *
     * @return string
     */
    protected function getTitleField(array &$args): string
    {
        /*
         * Use title_txt_LOCALE when search
         * is filtered by translation.
         */
        if (isset($args['locale']) && is_string($args['locale'])) {
            return 'title_txt_' . \Locale::getPrimaryLanguage($args['locale']);
        }
        if (isset($args['translation']) && $args['translation'] instanceof Translation) {
            return 'title_txt_' . \Locale::getPrimaryLanguage($args['translation']->getLocale());
        }
        return 'title';
    }

    /**
     * @param array $args
     *
     * @return string
     */
    protected function getTagsField(array &$args): string
    {
        /*
         * Use tags_txt_LOCALE when search
         * is filtered by translation.
         */
        if (isset($args['locale']) && is_string($args['locale'])) {
            return 'tags_txt_' . \Locale::getPrimaryLanguage($args['locale']);
        }
        if (isset($args['translation']) && $args['translation'] instanceof Translation) {
            return 'tags_txt_' . \Locale::getPrimaryLanguage($args['translation']->getLocale());
        }
        return 'tags_txt';
    }

    /**
     * Create Solr Select query. Override it to add DisMax fields and rules.
     *
     * @param array $args
     * @param int $rows
     * @param int $page
     * @return Query
     */
    protected function createSolrQuery(array &$args = [], int $rows = 20, int $page = 1): Query
    {
        $query = $this->getSolr()->createSelect();

        foreach ($args as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    $query->addFilterQuery([
                        "key" => "fq" . $k,
                        "query" => $v,
                    ]);
                }
            } elseif (is_scalar($value)) {
                $query->addParam($key, $value);
            }
        }
        /**
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
