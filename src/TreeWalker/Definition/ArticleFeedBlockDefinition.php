<?php

declare(strict_types=1);

namespace App\TreeWalker\Definition;

use App\GeneratedEntity\NSArticle;
use App\GeneratedEntity\NSArticleFeedBlock;
use Doctrine\ORM\Tools\Pagination\Paginator;
use RZ\Roadiz\CoreBundle\Api\TreeWalker\NodeSourceWalkerContext;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\TreeWalker\Definition\ContextualDefinitionTrait;
use RZ\TreeWalker\Definition\StoppableDefinition;
use RZ\TreeWalker\WalkerInterface;

final class ArticleFeedBlockDefinition implements StoppableDefinition
{
    use ContextualDefinitionTrait;

    public function isStoppingCollectionOnceInvoked(): bool
    {
        return true;
    }

    /**
     * @param NodesSources $source
     * @param WalkerInterface $walker
     * @return array
     * @throws \Exception
     */
    public function __invoke(NodesSources $source, WalkerInterface $walker): array
    {
        if ($this->context instanceof NodeSourceWalkerContext) {
            $this->context->getStopwatch()->start(self::class);
            if (!$source instanceof NSArticleFeedBlock) {
                throw new \InvalidArgumentException('Source must be instance of ' . NSArticleFeedBlock::class);
            }

            $criteria = [
                'node.visible' => true,
                'publishedAt' => ['<=', new \DateTime()],
                'translation' => $source->getTranslation(),
                'node.nodeType' => $this->context->getNodeTypesBag()->get('Article')
            ];

            // Prevent Article feed to list root Article again
            $root = $walker->getRoot()->getItem();
            if ($root instanceof NSArticle) {
                $criteria['id'] = ['!=', $root->getId()];
            }

            if (null !== $source->getNode() && \count($source->getNode()->getTags()) > 0) {
                $criteria['tags'] = $source->getNode()->getTags();
                $criteria['tagExclusive'] = true;
            }

            $count = (int) ($source->getListingCount() ?? 4);

            $children = $this->context->getNodeSourceApi()->getBy($criteria, [
                'publishedAt' => 'DESC'
            ], $count);


            if ($children instanceof Paginator) {
                $iterator = $children->getIterator();
                if ($iterator instanceof \ArrayIterator) {
                    $children = $iterator->getArrayCopy();
                } else {
                    throw new \RuntimeException('Unexpected iterator type');
                }
            }

            $this->context->getStopwatch()->stop(self::class);

            return $children;
        }
        throw new \InvalidArgumentException('Context should be instance of ' . NodeSourceWalkerContext::class);
    }
}
