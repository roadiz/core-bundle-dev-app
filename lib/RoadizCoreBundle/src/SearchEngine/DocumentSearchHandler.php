<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine;

use RZ\Roadiz\CoreBundle\Entity\Folder;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\SearchEngine\Event\DocumentSearchQueryEvent;

/**
 * @package RZ\Roadiz\CoreBundle\SearchEngine
 */
class DocumentSearchHandler extends AbstractSearchHandler
{
    /**
     * @param string  $q
     * @param array   $args
     * @param integer $rows
     * @param boolean $searchTags
     * @param integer $proximity Proximity matching: Lucene supports finding words are a within a specific distance away.
     * @param integer $page
     *
     * @return array|null
     */
    protected function nativeSearch(
        string $q,
        array $args = [],
        int $rows = 20,
        bool $searchTags = false,
        int $proximity = 1,
        int $page = 1
    ): ?array {
        if (empty($q)) {
            return null;
        }
        $query = $this->createSolrQuery($args, $rows, $page);
        $queryTxt = $this->buildQuery($q, $args, $searchTags, $proximity);
        $query->setQuery($queryTxt);

        /*
         * Only need these fields as Doctrine
         * will do the rest.
         */
        $query->setFields([
            'id',
            'sort',
            'document_type_s',
            SolariumDocumentTranslation::IDENTIFIER_KEY,
            'filename_s',
            'locale_s',
        ]);

        $this->logger->debug('[Solr] Request document search…', [
            'query' => $queryTxt,
            'fq' => $args["fq"] ?? [],
            'params' => $query->getParams(),
        ]);

        /** @var DocumentSearchQueryEvent $event */
        $event = $this->eventDispatcher->dispatch(
            new DocumentSearchQueryEvent($query, $args)
        );
        $query = $event->getQuery();

        $solrRequest = $this->getSolr()->execute($query);
        return $solrRequest->getData();
    }

    /**
     * @param array $args
     * @return array
     */
    protected function argFqProcess(array &$args): array
    {
        if (!isset($args["fq"])) {
            $args["fq"] = [];
        }

        /*
         * `all_tags_slugs_ss` can store all folders, even technical ones, this fields should not user-searchable.
         */
        if (!empty($args['folders'])) {
            if ($args['folders'] instanceof Folder) {
                $args["fq"][] = sprintf('all_tags_slugs_ss:"%s"', $args['folders']->getFolderName());
            } elseif (is_array($args['folders'])) {
                foreach ($args['folders'] as $folder) {
                    if ($folder instanceof Folder) {
                        $args["fq"][] = sprintf('all_tags_slugs_ss:"%s"', $folder->getFolderName());
                    }
                }
            }
            unset($args['folders']);
        }

        if (isset($args['mimeType'])) {
            $tmp = "mime_type_s:";
            if (!is_array($args['mimeType'])) {
                $tmp .= (string) $args['mimeType'];
            } else {
                $value = implode(' AND ', $args['mimeType']);
                $tmp .= '(' . $value . ')';
            }
            unset($args['mimeType']);
            $args["fq"][] = $tmp;
        }

        /*
         * Filter by translation or locale
         */
        if (isset($args['translation']) && $args['translation'] instanceof Translation) {
            $args["fq"][] = "locale_s:" . $args['translation']->getLocale();
        }
        if (isset($args['locale']) && is_string($args['locale'])) {
            $args["fq"][] = "locale_s:" . $args['locale'];
        }

        /*
         * Filter by filename
         */
        if (isset($args['filename'])) {
            $args["fq"][] = sprintf('filename_s:"%s"', trim($args['filename']));
        }

        /*
         * Filter out non-valid copyright documents
         */
        if (isset($args['copyrightValid'])) {
            $args["fq"][] = '(copyright_valid_since_dt:[* TO NOW] AND copyright_valid_until_dt:[NOW TO *])';
            unset($args['copyrightValid']);
        }

        return $args;
    }

    /**
     * @return string
     */
    protected function getDocumentType(): string
    {
        return 'DocumentTranslation';
    }
}
