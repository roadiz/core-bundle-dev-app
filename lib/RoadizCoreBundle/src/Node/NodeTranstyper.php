<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Node;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;
use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodesSourcesDocuments;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use RZ\Roadiz\CoreBundle\Entity\Redirection;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Entity\UrlAlias;

final class NodeTranstyper
{
    private ManagerRegistry $managerRegistry;
    private LoggerInterface $logger;

    public function __construct(
        ManagerRegistry $managerRegistry,
        private readonly NodeTypes $nodeTypesBag,
        ?LoggerInterface $logger = null,
    ) {
        $this->logger = $logger ?? new NullLogger();
        $this->managerRegistry = $managerRegistry;
    }

    private function getManager(): ObjectManager
    {
        $manager = $this->managerRegistry->getManagerForClass(NodesSources::class);
        if (null === $manager) {
            throw new \RuntimeException('No manager was found during trans-typing.');
        }

        return $manager;
    }

    /**
     * @return NodeTypeField|null
     */
    private function getMatchingNodeTypeField(
        NodeTypeFieldInterface $oldField,
        NodeTypeInterface $destinationNodeType,
    ): ?NodeTypeFieldInterface {
        $criteria = Criteria::create();
        $criteria->andWhere(Criteria::expr()->eq('name', $oldField->getName()))
            ->andWhere(Criteria::expr()->eq('type', $oldField->getType()))
            ->setMaxResults(1);
        $field = $destinationNodeType->getFields()->matching($criteria)->first();

        return $field ? $field : null;
    }

    /**
     * Warning, this method DOES NOT flush entityManager at the end.
     *
     * Trans-typing SHOULD be executed in one single transaction
     *
     * @see https://www.doctrine-project.org/projects/doctrine-orm/en/latest/reference/transactions-and-concurrency.html
     */
    public function transtype(Node $node, NodeTypeInterface $destinationNodeType, bool $mock = true): Node
    {
        /*
         * Get an association between old fields and new fields
         * to find data that can be transferred during trans-typing.
         */
        $fieldAssociations = [];
        $oldFields = $this->nodeTypesBag->get($node->getNodeTypeName())?->getFields() ?? [];

        foreach ($oldFields as $oldField) {
            $matchingField = $this->getMatchingNodeTypeField($oldField, $destinationNodeType);
            if (null !== $matchingField) {
                $fieldAssociations[] = [
                    $oldField, // old type field
                    $matchingField, // new type field
                ];
            }
        }
        $this->logger->debug('Get matching fields');

        /** @var class-string<NodesSources> $sourceClass */
        $sourceClass = $destinationNodeType->getSourceEntityFullQualifiedClassName();

        /*
         * Testing if new nodeSource class is available
         * and cache have been cleared before actually performing
         * trans-type, not to get an orphan node.
         */
        if ($mock) {
            $this->mockTranstype($destinationNodeType);
        }

        /*
         * Perform actual trans-typing
         */
        $existingSources = $node->getNodeSources()->toArray();
        $existingRedirections = [];
        /** @var NodesSources $existingSource */
        foreach ($existingSources as $existingSource) {
            $existingRedirections[$existingSource->getTranslation()->getLocale()] = array_map(function (Redirection $redirection) {
                $this->managerRegistry->getManager()->detach($redirection);

                return $redirection;
            }, $existingSource->getRedirections()->toArray());
        }

        $this->removeOldSources($node, $existingSources);

        /** @var NodesSources $existingSource */
        foreach ($existingSources as $existingSource) {
            $this->managerRegistry->getManager()->detach($existingSource);
            $this->doTranstypeSingleSource(
                $node,
                $existingSource,
                $existingSource->getTranslation(),
                $sourceClass,
                $fieldAssociations,
                $existingRedirections
            );
            $this->logger->debug('Transtyped: '.$existingSource->getTranslation()->getLocale());
        }

        $node->setNodeTypeName($destinationNodeType->getName());

        return $node;
    }

    protected function removeOldSources(Node $node, array &$sources): void
    {
        /** @var NodesSources $existingSource */
        foreach ($sources as $existingSource) {
            // First plan old source deletion.
            $node->removeNodeSources($existingSource);
            $this->getManager()->remove($existingSource);
        }
        // Flush once
        $this->getManager()->flush();
        $this->logger->debug('Removed old sources');
    }

    /**
     * Warning, this method DO NOT flush entityManager at the end.
     *
     * @param class-string<NodesSources> $sourceClass
     */
    protected function doTranstypeSingleSource(
        Node $node,
        NodesSources $existingSource,
        TranslationInterface $translation,
        string $sourceClass,
        array &$fieldAssociations,
        array &$existingRedirections,
    ): NodesSources {
        /** @var NodesSources $source */
        $source = new $sourceClass($node, $translation);
        $source = $source->withNodesSources($existingSource);
        $this->getManager()->persist($source);

        foreach ($fieldAssociations as $fields) {
            /** @var NodeTypeField $oldField */
            $oldField = $fields[0];
            /** @var NodeTypeField $matchingField */
            $matchingField = $fields[1];

            if (!$oldField->isVirtual()) {
                /*
                 * Copy simple data from source to another
                 */
                $setter = $oldField->getSetterName();
                $getter = $oldField->getGetterName();
                $source->$setter($existingSource->$getter());
            } elseif ($oldField->isDocuments()) {
                /*
                 * Copy documents.
                 */
                $documents = $existingSource->getDocumentsByFieldsWithName($oldField->getName());
                foreach ($documents as $document) {
                    $nsDoc = new NodesSourcesDocuments($source, $document);
                    // TODO: We are losing all contextual data here (hotspot, alignment, etc.)
                    $nsDoc->setFieldName($matchingField->getName());
                    $this->getManager()->persist($nsDoc);
                    $source->getDocumentsByFields()->add($nsDoc);
                }
            }
        }
        $this->logger->debug('Fill existing data');

        /*
         * Recreate url-aliases too.
         */
        /** @var UrlAlias $urlAlias */
        foreach ($existingSource->getUrlAliases() as $urlAlias) {
            $newUrlAlias = new UrlAlias();
            $newUrlAlias->setNodeSource($source);
            $this->getManager()->persist($newUrlAlias);
            $newUrlAlias->setAlias($urlAlias->getAlias());
            $source->addUrlAlias($newUrlAlias);
        }
        $this->logger->debug('Recreate aliases');

        /*
         * Recreate redirections too.
         */
        /** @var Redirection $existingRedirection */
        foreach ($existingRedirections[$translation->getLocale()] as $existingRedirection) {
            $newRedirection = new Redirection();
            $this->getManager()->persist($newRedirection);
            $newRedirection->setRedirectNodeSource($source);
            $newRedirection->setQuery($existingRedirection->getQuery());
            $newRedirection->setType($existingRedirection->getType());
        }
        $this->logger->debug('Recreate aliases');

        return $source;
    }

    /**
     * Warning, this method flushes entityManager.
     *
     * @throws \InvalidArgumentException if mock fails due to Source class not existing
     */
    protected function mockTranstype(NodeTypeInterface $nodeType): void
    {
        $sourceClass = $nodeType->getSourceEntityFullQualifiedClassName();
        if (!class_exists($sourceClass)) {
            throw new \InvalidArgumentException($sourceClass.' node-source class does not exist.');
        }
        $uniqueId = uniqid();
        /*
         * Testing if new nodeSource class is available
         * and cache have been cleared before actually performing
         * transtype, not to get an orphan node.
         */
        $node = new Node();
        $node->setNodeTypeName($nodeType->getName());
        $node->setNodeName('testing_before_transtype'.$uniqueId);
        $this->getManager()->persist($node);

        $translation = new Translation();
        $translation->setAvailable(true);
        $translation->setLocale(\mb_substr($uniqueId, 0, 10));
        $translation->setName('test'.$uniqueId);
        $this->getManager()->persist($translation);

        /** @var NodesSources $testSource */
        $testSource = new $sourceClass($node, $translation);
        $testSource->setTitle('testing_before_transtype'.$uniqueId);
        $this->getManager()->persist($testSource);
        $this->getManager()->flush();

        // then remove it if OK
        $this->getManager()->remove($testSource);
        $this->getManager()->remove($node);
        $this->getManager()->remove($translation);
        $this->getManager()->flush();
    }
}
