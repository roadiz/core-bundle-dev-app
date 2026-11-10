<?php

declare(strict_types=1);

namespace RZ\Roadiz\SolrBundle;

use Doctrine\Common\Collections\Collection;
use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\CoreBundle\Enum\NodeStatus;
use RZ\Roadiz\CoreBundle\SearchEngine\NodeSourceSearchHandlerInterface;
use RZ\Roadiz\SolrBundle\Event\AbstractSearchQueryEvent;
use RZ\Roadiz\SolrBundle\Event\NodeSourceSearchQueryEvent;
use RZ\Roadiz\SolrBundle\Solarium\SolariumNodeSource;
use Solarium\QueryType\Select\Query\Query;

class NodeSourceSearchHandler extends AbstractSearchHandler implements NodeSourceSearchHandlerInterface
{
    protected bool $boostByPublicationDate = false;
    protected bool $boostByUpdateDate = false;
    protected bool $boostByCreationDate = false;

    #[\Override]
    protected function getResultFields(): array
    {
        /*
         * Only need these fields as Doctrine
         * will do the rest.
         */
        return [
            'score',
            'id',
            'document_type_s',
            SolariumNodeSource::IDENTIFIER_KEY,
            'node_name_s',
            'locale_s',
        ];
    }

    #[\Override]
    protected function createSearchQueryEvent(Query $query, array $args): AbstractSearchQueryEvent
    {
        return new NodeSourceSearchQueryEvent($query, $args);
    }

    /**
     * Node-sources also index their slug, which document translations do not.
     */
    #[\Override]
    protected function buildQueryFields(array &$args, bool $searchTags = true): string
    {
        return parent::buildQueryFields($args, $searchTags).' slug_s';
    }

    #[\Override]
    protected function getBoostFunction(): ?string
    {
        if ($this->boostByPublicationDate) {
            return 'recip(ms(NOW,published_at_dt),3.16e-11,1,1)';
        }
        if ($this->boostByUpdateDate) {
            return 'recip(ms(NOW,updated_at_dt),3.16e-11,1,1)';
        }
        if ($this->boostByCreationDate) {
            return 'recip(ms(NOW,created_at_dt),3.16e-11,1,1)';
        }

        return null;
    }

    #[\Override]
    protected function argFqProcess(array &$args): array
    {
        $args['fq'] ??= [];

        $visible = $args['visible'] ?? $args['node.visible'] ?? null;
        if (isset($visible)) {
            $tmp = 'node_visible_b:'.(($visible) ? 'true' : 'false');
            unset($args['visible']);
            unset($args['node.visible']);
            $args['fq'][] = $tmp;
        }

        /*
         * filter by tag or tags
         * `all_tags_slugs_ss` can store all tags, even technical ones, this fields should not user-searchable.
         */
        if (!empty($args['tags'])) {
            if ($args['tags'] instanceof Tag) {
                $args['fq'][] = 'all_tags_slugs_ss:'.$this->escapePhrase($args['tags']->getTagName());
            } elseif (is_array($args['tags'])) {
                foreach ($args['tags'] as $tag) {
                    if ($tag instanceof Tag) {
                        $args['fq'][] = 'all_tags_slugs_ss:'.$this->escapePhrase($tag->getTagName());
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
                        $orQuery[] = $this->escapePhrase($singleNodeType->getName());
                    } elseif (is_string($singleNodeType)) {
                        $orQuery[] = $this->escapePhrase($singleNodeType);
                    }
                }
                $args['fq'][] = 'node_type_s:('.implode(' OR ', $orQuery).')';
            } elseif ($nodeType instanceof NodeTypeInterface) {
                $args['fq'][] = 'node_type_s:'.$this->escapePhrase($nodeType->getName());
            } else {
                $args['fq'][] = 'node_type_s:'.$this->escapePhrase((string) $nodeType);
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
                $args['fq'][] = 'node_parent_i:'.$parent->getId();
            } elseif (is_string($parent)) {
                $args['fq'][] = 'node_parent_s:'.$this->escapePhrase(trim($parent));
            } elseif (is_numeric($parent)) {
                $args['fq'][] = 'node_parent_i:'.(int) $parent;
            }
            unset($args['parent']);
            unset($args['node.parent']);
        }

        /*
         * Handle publication date-time filtering
         */
        $hasExplicitPublishedAtFilter = isset($args['publishedAt']);
        if (isset($args['publishedAt'])) {
            $tmp = 'published_at_dt:';
            if (!is_array($args['publishedAt']) && $args['publishedAt'] instanceof \DateTimeInterface) {
                $tmp .= $this->formatDateTimeToUTC($args['publishedAt']);
            } elseif (
                isset($args['publishedAt'][0])
                && 'BETWEEN' === $args['publishedAt'][0]
                && isset($args['publishedAt'][1])
                && $args['publishedAt'][1] instanceof \DateTimeInterface
                && isset($args['publishedAt'][2])
                && $args['publishedAt'][2] instanceof \DateTimeInterface
            ) {
                $tmp .= '['.
                    $this->formatDateTimeToUTC($args['publishedAt'][1]).
                    ' TO '.
                    $this->formatDateTimeToUTC($args['publishedAt'][2]).']';
            } elseif (
                isset($args['publishedAt'][0])
                && '<=' === $args['publishedAt'][0]
                && isset($args['publishedAt'][1])
                && $args['publishedAt'][1] instanceof \DateTimeInterface
            ) {
                $tmp .= '[* TO '.$this->formatDateTimeToUTC($args['publishedAt'][1]).']';
            } elseif (
                isset($args['publishedAt'][0])
                && '>=' === $args['publishedAt'][0]
                && isset($args['publishedAt'][1])
                && $args['publishedAt'][1] instanceof \DateTimeInterface
            ) {
                $tmp .= '['.$this->formatDateTimeToUTC($args['publishedAt'][1]).' TO *]';
            }
            unset($args['publishedAt']);
            $args['fq'][] = $tmp;
        }

        /*
         * Handle unpublication (expiration) date-time filtering
         */
        $hasExplicitUnpublishedAtFilter = isset($args['unpublishedAt']);
        if (isset($args['unpublishedAt'])) {
            $tmp = 'unpublished_at_dt:';
            if (!is_array($args['unpublishedAt']) && $args['unpublishedAt'] instanceof \DateTimeInterface) {
                $tmp .= $this->formatDateTimeToUTC($args['unpublishedAt']);
            } elseif (
                isset($args['unpublishedAt'][0])
                && 'BETWEEN' === $args['unpublishedAt'][0]
                && isset($args['unpublishedAt'][1])
                && $args['unpublishedAt'][1] instanceof \DateTimeInterface
                && isset($args['unpublishedAt'][2])
                && $args['unpublishedAt'][2] instanceof \DateTimeInterface
            ) {
                $tmp .= '['.
                    $this->formatDateTimeToUTC($args['unpublishedAt'][1]).
                    ' TO '.
                    $this->formatDateTimeToUTC($args['unpublishedAt'][2]).']';
            } elseif (
                isset($args['unpublishedAt'][0])
                && '<=' === $args['unpublishedAt'][0]
                && isset($args['unpublishedAt'][1])
                && $args['unpublishedAt'][1] instanceof \DateTimeInterface
            ) {
                $tmp .= '[* TO '.$this->formatDateTimeToUTC($args['unpublishedAt'][1]).']';
            } elseif (
                isset($args['unpublishedAt'][0])
                && '>=' === $args['unpublishedAt'][0]
                && isset($args['unpublishedAt'][1])
                && $args['unpublishedAt'][1] instanceof \DateTimeInterface
            ) {
                $tmp .= '['.$this->formatDateTimeToUTC($args['unpublishedAt'][1]).' TO *]';
            }
            unset($args['unpublishedAt']);
            $args['fq'][] = $tmp;
        }

        $status = $args['status'] ?? $args['node.status'] ?? null;
        if (isset($status)) {
            $tmp = 'node_status_i:';
            if ($status instanceof NodeStatus) {
                $tmp .= (string) $status->value;
            } elseif (is_numeric($status)) {
                $tmp .= (string) $status;
            } elseif (is_array($status) && '<=' == $status[0] && $status[1] instanceof NodeStatus) {
                $tmp .= '[* TO '.(string) $status[1]->value.']';
            } elseif (is_array($status) && '>=' == $status[0] && $status[1] instanceof NodeStatus) {
                $tmp .= '['.(string) $status[1]->value.' TO *]';
            }
            unset($args['status']);
            unset($args['node.status']);
            $args['fq'][] = $tmp;
        } else {
            /*
             * No explicit status requested: default to public visibility,
             * excluding embargoed content not yet published, same as
             * NodesSourcesRepository::alterQueryBuilderWithAuthorizationChecker.
             */
            $args['fq'][] = 'node_status_i:'.(string) NodeStatus::PUBLISHED->value;
            if (!$hasExplicitPublishedAtFilter) {
                $args['fq'][] = 'published_at_dt:[* TO NOW/MINUTE]';
            }
            if (!$hasExplicitUnpublishedAtFilter) {
                /*
                 * Exclude expired content: keep documents whose unpublished_at_dt is in the
                 * future or missing (unpublishedAt IS NULL OR unpublishedAt > now).
                 */
                $args['fq'][] = '(*:* -unpublished_at_dt:[* TO NOW/MINUTE])';
            }
        }

        /*
         * Filter by translation or locale
         */
        if (isset($args['translation']) && $args['translation'] instanceof TranslationInterface) {
            $args['fq'][] = 'locale_s:'.$this->escapePhrase($args['translation']->getLocale());
        }
        if (isset($args['locale']) && is_string($args['locale'])) {
            $args['fq'][] = 'locale_s:'.$this->escapePhrase($args['locale']);
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
