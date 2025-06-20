<?php

declare(strict_types=1);

namespace RZ\Roadiz\SolrBundle\Indexer;

use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;

final class NodeIndexer extends NodesSourcesIndexer
{
    #[\Override]
    public function index(mixed $id): void
    {
        $node = $this->managerRegistry->getRepository(Node::class)->find($id);
        if (null !== $node) {
            $update = $this->getSolr()->createUpdate();
            /** @var NodesSources $nodeSource */
            foreach ($node->getNodeSources() as $nodeSource) {
                $this->indexNodeSource($nodeSource, $update);
            }
            $update->addCommit(true, true, false);
            $this->getSolr()->update($update);
        }
    }

    #[\Override]
    public function delete(mixed $id): void
    {
        $node = $this->managerRegistry->getRepository(Node::class)->find($id);
        if (null !== $node) {
            foreach ($node->getNodeSources() as $nodeSource) {
                $this->deleteNodeSource($nodeSource);
            }

            // optimize the index
            $this->commitSolr();
        }
    }
}
