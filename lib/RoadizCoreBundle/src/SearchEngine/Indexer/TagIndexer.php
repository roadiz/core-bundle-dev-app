<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine\Indexer;

use RZ\Roadiz\CoreBundle\Entity\Tag;
use Solarium\Exception\HttpException;

final class TagIndexer extends NodesSourcesIndexer
{
    public function index(mixed $id): void
    {
        try {
            $tag = $this->managerRegistry->getRepository(Tag::class)->find($id);
            if (null === $tag) {
                return;
            }
            $update = $this->getSolr()->createUpdate();
            $nodes = $tag->getNodes();

            foreach ($nodes as $node) {
                foreach ($node->getNodeSources() as $nodeSource) {
                    $solrSource = $this->solariumFactory->createWithNodesSources($nodeSource);
                    $solrSource->getDocumentFromIndex();
                    $solrSource->update($update);
                }
            }
            $this->getSolr()->update($update);

            // then optimize
            $optimizeUpdate = $this->getSolr()->createUpdate();
            $optimizeUpdate->addOptimize(true, true, 5);
            $this->getSolr()->update($optimizeUpdate);
            // and commit
            $finalCommitUpdate = $this->getSolr()->createUpdate();
            $finalCommitUpdate->addCommit(true, true, false);
            $this->getSolr()->update($finalCommitUpdate);
        } catch (HttpException $exception) {
            $this->logger->error($exception->getMessage());
        }
    }

    public function delete(mixed $id): void
    {
        // Just reindex all linked NS to get rid of tag
        $this->index($id);
    }
}
