<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EntityHandler;

use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ObjectManager;
use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;
use RZ\Roadiz\Core\Handlers\AbstractHandler;
use RZ\Roadiz\CoreBundle\Bag\Settings;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodesSourcesDocuments;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use RZ\Roadiz\CoreBundle\Entity\Tag;

/**
 * Handle operations with node-sources entities.
 */
final class NodesSourcesHandler extends AbstractHandler
{
    private ?NodesSources $nodeSource = null;
    /**
     * @var array<NodesSources>|null
     */
    private ?array $parentsNodeSources = null;

    public function __construct(ObjectManager $objectManager, private readonly Settings $settingsBag)
    {
        parent::__construct($objectManager);
    }

    /**
     * @return EntityRepository<NodesSources>
     */
    protected function getRepository(): EntityRepository
    {
        return $this->objectManager->getRepository(NodesSources::class);
    }

    public function getNodeSource(): NodesSources
    {
        if (null === $this->nodeSource) {
            throw new \BadMethodCallException('NodesSources is null');
        }

        return $this->nodeSource;
    }

    /**
     * @return $this
     */
    public function setNodeSource(NodesSources $nodeSource): self
    {
        $this->nodeSource = $nodeSource;

        return $this;
    }

    /**
     * Remove every node-source documents associations for a given field.
     *
     * @param NodeTypeField $field
     *
     * @return $this
     */
    public function cleanDocumentsFromField(NodeTypeFieldInterface $field, bool $flush = true): self
    {
        $this->nodeSource->clearDocumentsByFields($field);

        if (true === $flush) {
            $this->objectManager->flush();
        }

        return $this;
    }

    /**
     * Add a document to current node-source for a given node-type field.
     *
     * @param NodeTypeField $field
     *
     * @return $this
     */
    public function addDocumentForField(
        Document $document,
        NodeTypeFieldInterface $field,
        bool $flush = true,
        ?float $position = null,
    ): self {
        $nsDoc = new NodesSourcesDocuments($this->nodeSource, $document, $field);

        if (!$this->nodeSource->hasNodesSourcesDocuments($nsDoc)) {
            if (null === $position) {
                $latestPosition = $this->objectManager
                    ->getRepository(NodesSourcesDocuments::class)
                    ->getLatestPositionForFieldName($this->nodeSource, $field->getName());

                $nsDoc->setPosition($latestPosition + 1);
            } else {
                $nsDoc->setPosition($position);
            }
            $this->nodeSource->addDocumentsByFields($nsDoc);
            $this->objectManager->persist($nsDoc);
            if (true === $flush) {
                $this->objectManager->flush();
            }
        }

        return $this;
    }

    /**
     * Get documents linked to current node-source for a given field name.
     *
     * @param string $fieldName Name of the node-type field
     *
     * @return array<Document>
     *
     * @deprecated Use directly NodesSources::getDocumentsByFieldsWithName
     */
    public function getDocumentsFromFieldName(string $fieldName): array
    {
        return $this->objectManager
            ->getRepository(Document::class)
            ->findByNodeSourceAndFieldName(
                $this->nodeSource,
                $fieldName
            );
    }

    /**
     * Get a string describing uniquely the current nodeSource.
     *
     * Can be the urlAlias or the nodeName
     *
     * @deprecated Use directly NodesSources::getIdentifier
     */
    public function getIdentifier(): string
    {
        $urlAlias = $this->nodeSource->getUrlAliases()->first();
        if (is_object($urlAlias)) {
            return $urlAlias->getAlias();
        }

        return $this->nodeSource->getNode()->getNodeName();
    }

    /**
     * Get parent node-source to get the current translation.
     *
     * @deprecated Use directly NodesSources::getParent
     */
    public function getParent(): ?NodesSources
    {
        return $this->nodeSource->getParent();
    }

    /**
     * Get every nodeSources parents from direct parent to farest ancestor.
     *
     * @return array<NodesSources>
     *
     * @deprecated Use NodesSourcesRepository::findParents
     */
    public function getParents(
        ?array $criteria = null,
    ): array {
        if (null === $this->parentsNodeSources) {
            $this->parentsNodeSources = [];

            if (null === $criteria) {
                $criteria = [];
            }

            $parent = $this->nodeSource;

            while (null !== $parent) {
                $criteria = array_merge(
                    $criteria,
                    [
                        'node' => $parent->getNode()->getParent(),
                        'translation' => $this->nodeSource->getTranslation(),
                    ]
                );
                $currentParent = $this->getRepository()->findOneBy(
                    $criteria,
                    []
                );

                if (null !== $currentParent) {
                    $this->parentsNodeSources[] = $currentParent;
                }

                $parent = $currentParent;
            }
        }

        return $this->parentsNodeSources;
    }

    /**
     * Get children nodes sources to lock with current translation.
     *
     * @param array|null $criteria Additional criteria
     * @param array|null $order    Non default ordering
     *
     * @return array<object|NodesSources>
     *
     * @deprecated Use TreeWalker or NodesSourcesRepository::findChildren
     */
    public function getChildren(
        ?array $criteria = null,
        ?array $order = null,
    ): array {
        $defaultCrit = [
            'node.parent' => $this->nodeSource->getNode(),
            'translation' => $this->nodeSource->getTranslation(),
        ];

        if (null !== $order) {
            $defaultOrder = $order;
        } else {
            $defaultOrder = [
                'node.position' => 'ASC',
            ];
        }

        if (null !== $criteria) {
            $defaultCrit = array_merge($defaultCrit, $criteria);
        }

        return $this->getRepository()->findBy(
            $defaultCrit,
            $defaultOrder
        );
    }

    /**
     * Get first node-source among current node-source children.
     *
     * @deprecated Use NodesSourcesRepository::findFirstChild
     */
    public function getFirstChild(
        ?array $criteria = null,
        ?array $order = null,
    ): ?NodesSources {
        $defaultCrit = [
            'node.parent' => $this->nodeSource->getNode(),
            'translation' => $this->nodeSource->getTranslation(),
        ];

        if (null !== $order) {
            $defaultOrder = $order;
        } else {
            $defaultOrder = [
                'node.position' => 'ASC',
            ];
        }

        if (null !== $criteria) {
            $defaultCrit = array_merge($defaultCrit, $criteria);
        }

        return $this->getRepository()->findOneBy(
            $defaultCrit,
            $defaultOrder
        );
    }

    /**
     * Get last node-source among current node-source children.
     *
     * @deprecated Use NodesSourcesRepository::findLastChild
     */
    public function getLastChild(
        ?array $criteria = null,
        ?array $order = null,
    ): ?NodesSources {
        $defaultCrit = [
            'node.parent' => $this->nodeSource->getNode(),
            'translation' => $this->nodeSource->getTranslation(),
        ];

        if (null !== $order) {
            $defaultOrder = $order;
        } else {
            $defaultOrder = [
                'node.position' => 'DESC',
            ];
        }

        if (null !== $criteria) {
            $defaultCrit = array_merge($defaultCrit, $criteria);
        }

        return $this->getRepository()->findOneBy(
            $defaultCrit,
            $defaultOrder
        );
    }

    /**
     * Get first node-source in the same parent as current node-source.
     *
     * @deprecated Use NodesSourcesRepository::findFirstSibling
     */
    public function getFirstSibling(
        ?array $criteria = null,
        ?array $order = null,
    ): ?NodesSources {
        if (null !== $this->nodeSource->getParent()) {
            $parentHandler = new NodesSourcesHandler($this->objectManager, $this->settingsBag);
            $parentHandler->setNodeSource($this->nodeSource->getParent());

            return $parentHandler->getFirstChild($criteria, $order);
        } else {
            $criteria['node.parent'] = null;

            return $this->getFirstChild($criteria, $order);
        }
    }

    /**
     * Get last node-source in the same parent as current node-source.
     *
     * @deprecated Use NodesSourcesRepository::findLastSibling
     */
    public function getLastSibling(
        ?array $criteria = null,
        ?array $order = null,
    ): ?NodesSources {
        if (null !== $this->nodeSource->getParent()) {
            $parentHandler = new NodesSourcesHandler($this->objectManager, $this->settingsBag);
            $parentHandler->setNodeSource($this->nodeSource->getParent());

            return $parentHandler->getLastChild($criteria, $order);
        } else {
            $criteria['node.parent'] = null;

            return $this->getLastChild($criteria, $order);
        }
    }

    /**
     * Get previous node-source from hierarchy.
     *
     * @deprecated Use NodesSourcesRepository::findPrevious
     */
    public function getPrevious(
        ?array $criteria = null,
        ?array $order = null,
    ): ?NodesSources {
        if ($this->nodeSource->getNode()->getPosition() <= 1) {
            return null;
        }

        $defaultCriteria = [
            /*
             * Use < operator to get first next nodeSource
             * even if it’s not the next position index
             */
            'node.position' => [
                '<',
                $this->nodeSource
                     ->getNode()
                     ->getPosition(),
            ],
            'node.parent' => $this->nodeSource->getNode()->getParent(),
            'translation' => $this->nodeSource->getTranslation(),
        ];
        if (null !== $criteria) {
            $defaultCriteria = array_merge($defaultCriteria, $criteria);
        }

        if (null === $order) {
            $order = [];
        }

        $order['node.position'] = 'DESC';

        return $this->getRepository()->findOneBy(
            $defaultCriteria,
            $order
        );
    }

    /**
     * Get next node-source from hierarchy.
     *
     * @deprecated Use NodesSourcesRepository::findNext
     */
    public function getNext(
        ?array $criteria = null,
        ?array $order = null,
    ): ?NodesSources {
        $defaultCrit = [
            /*
             * Use > operator to get first next nodeSource
             * even if it’s not the next position index
             */
            'node.position' => [
                '>',
                $this->nodeSource
                     ->getNode()
                     ->getPosition(),
            ],
            'node.parent' => $this->nodeSource->getNode()->getParent(),
            'translation' => $this->nodeSource->getTranslation(),
        ];
        if (null !== $criteria) {
            $defaultCrit = array_merge($defaultCrit, $criteria);
        }

        if (null === $order) {
            $order = [];
        }

        $order['node.position'] = 'ASC';

        return $this->getRepository()->findOneBy(
            $defaultCrit,
            $order
        );
    }

    /**
     * Get node tags with current source translation.
     *
     * @return iterable<Tag>
     *
     * @deprecated Use TagRepository::findByNodesSources
     */
    public function getTags(): iterable
    {
        /*
         * @phpstan-ignore-next-line
         */
        return $this->objectManager->getRepository(Tag::class)->findBy([
            'nodes' => $this->nodeSource->getNode(),
            'translation' => $this->nodeSource->getTranslation(),
        ], [
            'position' => 'ASC',
        ]);
    }

    /**
     * Get current node-source SEO data.
     *
     * This method returns a 3-fields array with:
     *
     * * title
     * * description
     * * keywords
     *
     * @return array<string>
     */
    public function getSEO(): array
    {
        return [
            'title' => ('' != $this->nodeSource->getMetaTitle()) ?
            $this->nodeSource->getMetaTitle() :
            $this->nodeSource->getTitle().' – '.$this->settingsBag->get('site_name'),
            'description' => ('' != $this->nodeSource->getMetaDescription()) ?
            $this->nodeSource->getMetaDescription() :
            $this->nodeSource->getTitle().', '.$this->settingsBag->get('seo_description'),
        ];
    }

    /**
     * Get nodes linked to current node for a given fieldname.
     *
     * @param string $fieldName Name of the node-type field
     *
     * @return array<Node> Collection of nodes
     *
     * @deprecated
     */
    public function getNodesFromFieldName(string $fieldName): array
    {
        $field = $this->nodeSource->getNode()->getNodeType()->getFieldByName($fieldName);
        if (null !== $field) {
            return $this->objectManager
                ->getRepository(Node::class)
                ->findByNodeAndFieldAndTranslation(
                    $this->nodeSource->getNode(),
                    $field,
                    $this->nodeSource->getTranslation()
                );
        }

        return [];
    }

    /**
     * Get nodes which own a reference to current node for a given fieldname.
     *
     * @param string $fieldName Name of the node-type field
     *
     * @return array<Node> Collection of nodes
     *
     * @deprecated
     */
    public function getReverseNodesFromFieldName(string $fieldName): array
    {
        $field = $this->nodeSource->getNode()->getNodeType()->getFieldByName($fieldName);
        if (null !== $field) {
            return $this->objectManager
                ->getRepository(Node::class)
                ->findByReverseNodeAndFieldAndTranslation(
                    $this->nodeSource->getNode(),
                    $field,
                    $this->nodeSource->getTranslation()
                );
        }

        return [];
    }
}
