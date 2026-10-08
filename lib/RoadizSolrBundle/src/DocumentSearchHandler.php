<?php

declare(strict_types=1);

namespace RZ\Roadiz\SolrBundle;

use RZ\Roadiz\CoreBundle\Entity\Folder;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\SolrBundle\Event\AbstractSearchQueryEvent;
use RZ\Roadiz\SolrBundle\Event\DocumentSearchQueryEvent;
use RZ\Roadiz\SolrBundle\Solarium\SolariumDocumentTranslation;
use Solarium\QueryType\Select\Query\Query;

class DocumentSearchHandler extends AbstractSearchHandler
{
    #[\Override]
    protected function getResultFields(): array
    {
        /*
         * Only need these fields as Doctrine
         * will do the rest.
         */
        return [
            'id',
            'sort',
            'document_type_s',
            SolariumDocumentTranslation::IDENTIFIER_KEY,
            'filename_s',
            'locale_s',
        ];
    }

    #[\Override]
    protected function createSearchQueryEvent(Query $query, array $args): AbstractSearchQueryEvent
    {
        return new DocumentSearchQueryEvent($query, $args);
    }

    #[\Override]
    protected function argFqProcess(array &$args): array
    {
        $args['fq'] ??= [];

        /*
         * `all_tags_slugs_ss` can store all folders, even technical ones, this fields should not user-searchable.
         */
        if (!empty($args['folders'])) {
            if ($args['folders'] instanceof Folder) {
                $args['fq'][] = 'all_tags_slugs_ss:'.$this->escapePhrase($args['folders']->getFolderName());
            } elseif (is_array($args['folders'])) {
                foreach ($args['folders'] as $folder) {
                    if ($folder instanceof Folder) {
                        $args['fq'][] = 'all_tags_slugs_ss:'.$this->escapePhrase($folder->getFolderName());
                    }
                }
            }
            unset($args['folders']);
        }

        if (isset($args['mimeType'])) {
            $tmp = 'mime_type_s:';
            if (!is_array($args['mimeType'])) {
                $tmp .= $this->escapePhrase((string) $args['mimeType']);
            } else {
                $value = implode(' AND ', array_map(
                    fn ($mimeType) => $this->escapePhrase((string) $mimeType),
                    $args['mimeType']
                ));
                $tmp .= '('.$value.')';
            }
            unset($args['mimeType']);
            $args['fq'][] = $tmp;
        }

        /*
         * Filter by translation or locale
         */
        if (isset($args['translation']) && $args['translation'] instanceof Translation) {
            $args['fq'][] = 'locale_s:'.$this->escapePhrase($args['translation']->getLocale());
        }
        if (isset($args['locale']) && is_string($args['locale'])) {
            $args['fq'][] = 'locale_s:'.$this->escapePhrase($args['locale']);
        }

        /*
         * Filter by filename
         */
        if (isset($args['filename'])) {
            $args['fq'][] = 'filename_s:'.$this->escapePhrase(trim((string) $args['filename']));
        }

        /*
         * Filter out non-valid copyright documents
         */
        if (isset($args['copyrightValid'])) {
            $args['fq'][] = '(copyright_valid_since_dt:[* TO NOW/MINUTE] AND copyright_valid_until_dt:[NOW/MINUTE TO *])';
            unset($args['copyrightValid']);
        }

        return $args;
    }

    #[\Override]
    protected function getDocumentType(): string
    {
        return 'DocumentTranslation';
    }
}
