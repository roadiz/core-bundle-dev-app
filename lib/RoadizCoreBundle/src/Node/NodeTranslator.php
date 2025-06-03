<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Node;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesCreatedEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final readonly class NodeTranslator
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
        private EventDispatcherInterface $dispatcher,
    ) {
    }

    public function translateNode(
        ?Translation $sourceTranslation,
        Translation $destinationTranslation,
        Node $node,
        bool $translateChildren = false,
    ): Node {
        $this->translateSingleNode($sourceTranslation, $destinationTranslation, $node);

        if ($translateChildren) {
            /** @var Node $child */
            foreach ($node->getChildren() as $child) {
                $this->translateNode($sourceTranslation, $destinationTranslation, $child, $translateChildren);
            }
        }

        return $node;
    }

    private function translateSingleNode(
        ?Translation $sourceTranslation,
        Translation $destinationTranslation,
        Node $node,
    ): NodesSources {
        /** @var NodesSources|null $existing */
        $existing = $this->managerRegistry
            ->getRepository(NodesSources::class)
            ->setDisplayingAllNodesStatuses(true)
            ->setDisplayingNotPublishedNodes(true)
            ->findOneByNodeAndTranslation($node, $destinationTranslation);

        if (null === $existing) {
            /** @var NodesSources|false $baseSource */
            $baseSource =
                $node->getNodeSourcesByTranslation($sourceTranslation)->first() ?:
                    $node->getNodeSources()->filter(fn (NodesSources $nodesSources) => $nodesSources->getTranslation()->isDefaultTranslation())->first() ?:
                        $node->getNodeSources()->first();

            if (!($baseSource instanceof NodesSources)) {
                throw new \RuntimeException('Cannot translate a Node without any NodesSources');
            }
            $source = clone $baseSource;
            $this->managerRegistry->getManager()->persist($source);

            foreach ($source->getDocumentsByFields() as $document) {
                $this->managerRegistry->getManager()->persist($document);
            }
            $source->setTranslation($destinationTranslation);
            $source->setNode($node);

            /*
             * Dispatch event
             */
            $this->dispatcher->dispatch(new NodesSourcesCreatedEvent($source));

            return $source;
        } else {
            return $existing;
        }
    }
}
