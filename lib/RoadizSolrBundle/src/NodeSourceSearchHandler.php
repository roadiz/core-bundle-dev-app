<?php

declare(strict_types=1);

namespace RZ\Roadiz\SolrBundle;

use Doctrine\Common\Collections\Collection;
use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\CoreBundle\SearchEngine\NodeSourceSearchHandlerInterface;
use RZ\Roadiz\SolrBundle\Event\NodeSourceSearchQueryEvent;
use RZ\Roadiz\SolrBundle\Solarium\SolariumNodeSource;

class NodeSourceSearchHandler extends AbstractSearchHandler implements NodeSourceSearchHandlerInterface
{
    protected bool $boostByPublicationDate = false;
    protected bool $boostByUpdateDate = false;
    protected bool $boostByCreationDate = false;

    #[\Override]
    protected function nativeSearch(
        string $q,
        array $args = [],
        int $rows = 20,
        bool $searchTags = false,
        int $page = 1,
    ): ?array {
        if (empty($q)) {
            return null;
        }
        $query = $this->createSolrQuery($args, $rows, $page);
        $queryTxt = $this->buildQuery($q, $args, $searchTags);

        if ($this->boostByPublicationDate) {
            $boost = '{!boost b=recip(ms(NOW,published_at_dt),3.16e-11,1,1)}';
            $queryTxt = $boost.$queryTxt;
        }
        if ($this->boostByUpdateDate) {
            $boost = '{!boost b=recip(ms(NOW,updated_at_dt),3.16e-11,1,1)}';
            $queryTxt = $boost.$queryTxt;
        }
        if ($this->boostByCreationDate) {
            $boost = '{!boost b=recip(ms(NOW,created_at_dt),3.16e-11,1,1)}';
            $queryTxt = $boost.$queryTxt;
        }

        $query->setQuery($queryTxt);

        /*
         * Only need these fields as Doctrine
         * will do the rest.
         */
        $query->setFields([
            'score',
            'id',
            'document_type_s',
            SolariumNodeSource::IDENTIFIER_KEY,
            'node_name_s',
            'locale_s',
        ]);

        $this->logger->debug('[Solr] Request node-sources search…', [
            'query' => $queryTxt,
            'fq' => $args['fq'] ?? [],
            'params' => $query->getParams(),
        ]);

        /** @var NodeSourceSearchQueryEvent $event */
        $event = $this->eventDispatcher->dispatch(
            new NodeSourceSearchQueryEvent($query, $args)
        );
        $query = $event->getQuery();

        $solrRequest = $this->getSolr()->execute($query);

        return $solrRequest->getData();
    }

    protected function filterByStatus(array &$args): array
    {
        /*
         * Handle publication date-time filtering
         */
        $fq = [];
        $now = new \DateTime();

        if (!isset($args['publishedAt'])) {
            $fq['published_at_dt'] = 'published_at_dt:[* TO NOW]';

            return $fq;
        }

        if (
            !is_array($args['publishedAt'])
            && $args['publishedAt'] instanceof \DateTimeInterface
            && $args['publishedAt'] < $now
        ) {
            $fq['published_at_dt'] = sprintf(
                'published_at_dt:%s',
                $this->formatDateTimeToUTC($args['publishedAt']),
            );
        } elseif (
            isset($args['publishedAt'][0])
            && 'BETWEEN' === $args['publishedAt'][0]
            && isset($args['publishedAt'][1])
            && $args['publishedAt'][1] instanceof \DateTimeInterface
            && isset($args['publishedAt'][2])
            && $args['publishedAt'][2] instanceof \DateTimeInterface
            && $args['publishedAt'][1] < $args['publishedAt'][2]
            && $args['publishedAt'][2] < $now
        ) {
            $fq['published_at_dt'] = sprintf(
                'published_at_dt:[%s TO %s]',
                $this->formatDateTimeToUTC($args['publishedAt'][1]),
                $this->formatDateTimeToUTC($args['publishedAt'][2]).']',
            );
        } elseif (
            isset($args['publishedAt'][0])
            && $args['publishedAt'][0] instanceof \DateTimeInterface
            && isset($args['publishedAt'][1])
            && $args['publishedAt'][1] instanceof \DateTimeInterface
            && $args['publishedAt'][0] < $args['publishedAt'][1]
            && $args['publishedAt'][1] < $now
        ) {
            $fq['published_at_dt'] = sprintf(
                'published_at_dt:[%s TO %s]',
                $this->formatDateTimeToUTC($args['publishedAt'][0]),
                $this->formatDateTimeToUTC($args['publishedAt'][1]).']',
            );
        } elseif (
            isset($args['publishedAt'][0])
            && '<=' === $args['publishedAt'][0]
            && isset($args['publishedAt'][1])
            && $args['publishedAt'][1] instanceof \DateTimeInterface
            && $args['publishedAt'][1] < $now
        ) {
            $fq['published_at_dt'] = sprintf(
                'published_at_dt:[* TO %s]',
                $this->formatDateTimeToUTC($args['publishedAt'][1]),
            );
        } elseif (
            isset($args['publishedAt'][0])
            && '>=' === $args['publishedAt'][0]
            && isset($args['publishedAt'][1])
            && $args['publishedAt'][1] instanceof \DateTimeInterface
            && $args['publishedAt'][1] < $now
        ) {
            $fq['published_at_dt'] = sprintf(
                'published_at_dt:[%s TO NOW]',
                $this->formatDateTimeToUTC($args['publishedAt'][1]),
            );
        } else {
            $fq['published_at_dt'] = 'published_at_dt:[* TO NOW]';
        }
        unset($args['publishedAt']);

        return $fq;
    }

    #[\Override]
    protected function argFqProcess(array &$args): array
    {
        if (!isset($args['fq'])) {
            $args['fq'] = [];
        }

        $args['fq'] = [
            ...$args['fq'],
            ...$this->filterByStatus($args),
        ];

        $visible = $args['visible'] ?? $args['node.visible'] ?? null;
        if (isset($visible)) {
            $tmp = 'node_visible_b:'.(($visible) ? 'true' : 'false');
            unset($args['visible']);
            unset($args['node.visible']);
            $args['fq']['node_visible_b'] = $tmp;
        }

        /*
         * filter by tag or tags
         * `all_tags_slugs_ss` can store all tags, even technical ones, this fields should not user-searchable.
         */
        if (!empty($args['tags'])) {
            if ($args['tags'] instanceof Tag) {
                $args['fq'][] = sprintf('all_tags_slugs_ss:"%s"', $args['tags']->getTagName());
            } elseif (is_array($args['tags'])) {
                foreach ($args['tags'] as $tag) {
                    if ($tag instanceof Tag) {
                        $args['fq']['tags'] = sprintf('all_tags_slugs_ss:"%s"', $tag->getTagName());
                    }
                }
            }
            unset($args['tags']);
        }

        /*
         * Filter by Node type
         */
        $nodeType = $args['nodeType'] ?? $args['nodeTypeName'] ?? $args['node.nodeTypeName'] ?? $args['node.nodeType'] ?? null;
        if (!empty($nodeType)) {
            if (is_array($nodeType) || $nodeType instanceof Collection) {
                $orQuery = [];
                foreach ($nodeType as $singleNodeType) {
                    if ($singleNodeType instanceof NodeTypeInterface) {
                        $orQuery[] = $singleNodeType->getName();
                    } elseif (is_string($singleNodeType)) {
                        $orQuery[] = $singleNodeType;
                    }
                }
                $args['fq']['node_type_s'] = 'node_type_s:('.implode(' OR ', $orQuery).')';
            } elseif ($nodeType instanceof NodeTypeInterface) {
                $args['fq']['node_type_s'] = 'node_type_s:'.$nodeType->getName();
            } else {
                $args['fq']['node_type_s'] = 'node_type_s:'.$nodeType;
            }
            unset($args['nodeType']);
            unset($args['node.nodeType']);
            unset($args['node.nodeTypeName']);
            unset($args['nodeTypeName']);
        }

        /*
         * Filter by parent node
         */
        $parent = $args['parent'] ?? $args['node.parent'] ?? null;
        if (!empty($parent)) {
            if ($parent instanceof Node) {
                $args['fq']['node_parent'] = 'node_parent_i:'.$parent->getId();
            } elseif (is_string($parent)) {
                $args['fq']['node_parent'] = 'node_parent_s:'.trim($parent);
            } elseif (is_numeric($parent)) {
                $args['fq']['node_parent'] = 'node_parent_i:'.(int) $parent;
            }
            unset($args['parent']);
            unset($args['node.parent']);
        }

        /*
         * Filter by translation or locale
         */
        if (isset($args['translation']) && $args['translation'] instanceof TranslationInterface) {
            $args['fq']['locale'] = 'locale_s:'.$args['translation']->getLocale();
        }
        if (isset($args['locale']) && is_string($args['locale'])) {
            $args['fq']['locale'] = 'locale_s:'.$args['locale'];
        }

        return $args;
    }

    #[\Override]
    protected function getDocumentType(): string
    {
        return 'NodesSources';
    }

    #[\Override]
    public function boostByPublicationDate(): NodeSourceSearchHandler
    {
        $this->boostByPublicationDate = true;
        $this->boostByUpdateDate = false;
        $this->boostByCreationDate = false;

        return $this;
    }

    #[\Override]
    public function boostByUpdateDate(): NodeSourceSearchHandler
    {
        $this->boostByPublicationDate = false;
        $this->boostByUpdateDate = true;
        $this->boostByCreationDate = false;

        return $this;
    }

    #[\Override]
    public function boostByCreationDate(): NodeSourceSearchHandler
    {
        $this->boostByPublicationDate = false;
        $this->boostByUpdateDate = false;
        $this->boostByCreationDate = true;

        return $this;
    }
}
