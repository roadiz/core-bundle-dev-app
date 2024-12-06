<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Node;

use Doctrine\Persistence\ObjectManager;
use RZ\Roadiz\CoreBundle\Entity\AttributeValue;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodesSourcesDocuments;
use RZ\Roadiz\CoreBundle\Entity\NodesToNodes;
use RZ\Roadiz\CoreBundle\Enum\NodeStatus;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

/**
 * Handles node duplication.
 */
#[Exclude]
final readonly class NodeDuplicator
{
    public function __construct(
        private Node $originalNode,
        private ObjectManager $objectManager,
        private NodeNamePolicyInterface $nodeNamePolicy,
    ) {
    }

    /**
     * Warning this method flush entityManager at its end.
     */
    public function duplicate(): Node
    {
        $this->objectManager->refresh($this->originalNode);

        if ($this->originalNode->isLocked()) {
            throw new \RuntimeException('Locked node cannot be duplicated.');
        }

        $parent = $this->originalNode->getParent();
        $node = clone $this->originalNode;

        if ($this->objectManager->contains($node)) {
            $this->objectManager->clear();
        }

        if (null !== $parent) {
            /** @var Node $parent */
            $parent = $this->objectManager->find(Node::class, $parent->getId());
            $node->setParent($parent);
        }
        // Demote cloned node to draft
        $node->setStatus(NodeStatus::DRAFT);

        $node = $this->doDuplicate($node);
        $this->objectManager->flush();
        $this->objectManager->refresh($node);

        return $node;
    }

    /**
     * Warning, do not do any FLUSH here to preserve transactional integrity.
     */
    private function doDuplicate(Node &$node): Node
    {
        $nodeSource = $node->getNodeSources()->first();
        if (false === $nodeSource) {
            throw new \RuntimeException('Node source is missing.');
        }
        $node->setNodeName(
            $this->nodeNamePolicy->getSafeNodeName($nodeSource)
        );

        /** @var Node $child */
        foreach ($node->getChildren() as $child) {
            $child->setParent($node);
            $this->doDuplicate($child);
        }

        /** @var NodesSources $nodeSource */
        foreach ($node->getNodeSources() as $nodeSource) {
            $this->objectManager->persist($nodeSource);

            /** @var NodesSourcesDocuments $nsDoc */
            foreach ($nodeSource->getDocumentsByFields() as $nsDoc) {
                $nsDoc->setNodeSource($nodeSource);
                $doc = $nsDoc->getDocument();
                $nsDoc->setDocument($doc);
                $nsDoc->setFieldName($nsDoc->getFieldName());
                $this->objectManager->persist($nsDoc);
            }
        }

        /*
         * Duplicate Node to Node relationship
         */
        $this->doDuplicateNodeRelations($node);
        /*
         * Duplicate Node attributes values
         */
        /** @var AttributeValue $attributeValue */
        foreach ($node->getAttributeValues() as $attributeValue) {
            $this->objectManager->persist($attributeValue);
            foreach ($attributeValue->getAttributeValueTranslations() as $attributeValueTranslation) {
                $this->objectManager->persist($attributeValueTranslation);
            }
        }

        /*
         * Persist duplicated node
         */
        $this->objectManager->persist($node);

        return $node;
    }

    /**
     * Duplicate Node to Node relationship.
     *
     * Warning, do not do any FLUSH here to preserve transactional integrity.
     */
    private function doDuplicateNodeRelations(Node $node): void
    {
        /** @var NodesToNodes[] $nodeRelations */
        $nodeRelations = $node->getBNodes()->toArray();
        foreach ($nodeRelations as $position => $nodeRelation) {
            $ntn = new NodesToNodes($node, $nodeRelation->getNodeB());
            $ntn->setFieldName($nodeRelation->getFieldName());
            $ntn->setPosition($position);
            $this->objectManager->persist($ntn);
        }
    }
}
