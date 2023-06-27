<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Node;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Entity\UrlAlias;
use RZ\Roadiz\CoreBundle\Repository\NodeRepository;
use RZ\Roadiz\CoreBundle\Repository\UrlAliasRepository;

final class NodeFactory
{
    private ManagerRegistry $managerRegistry;
    private NodeNamePolicyInterface $nodeNamePolicy;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param NodeNamePolicyInterface $nodeNamePolicy
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        NodeNamePolicyInterface $nodeNamePolicy
    ) {
        $this->nodeNamePolicy = $nodeNamePolicy;
        $this->managerRegistry = $managerRegistry;
    }

    public function create(
        string $title,
        ?NodeTypeInterface $type = null,
        ?TranslationInterface $translation = null,
        ?Node $node = null,
        ?Node $parent = null
    ): Node {
        /** @var NodeRepository $repository */
        $repository = $this->managerRegistry->getRepository(Node::class)
            ->setDisplayingAllNodesStatuses(true);

        if ($node === null && $type === null) {
            throw new \RuntimeException('Cannot create node from null NodeType and null Node.');
        }

        if ($translation === null) {
            $translation = $this->managerRegistry->getRepository(Translation::class)->findDefault();
        }

        if ($node === null) {
            $node = new Node($type);
        }

        if ($type instanceof NodeType) {
            $node->setTtl($type->getDefaultTtl());
        }
        if (null !== $parent) {
            $node->setParent($parent);
        }

        $sourceClass = $node->getNodeType()->getSourceEntityFullQualifiedClassName();
        /** @var NodesSources $source */
        $source = new $sourceClass($node, $translation);
        $manager = $this->managerRegistry->getManagerForClass(NodesSources::class);
        $source->injectObjectManager($manager);
        $source->setTitle($title);
        $source->setPublishedAt(new \DateTime());

        /*
         * Name node against policy
         */
        $nodeName = $this->nodeNamePolicy->getCanonicalNodeName($source);
        if (empty($nodeName)) {
            throw new \RuntimeException('Node name is empty.');
        }
        if (true === $repository->exists($nodeName)) {
            $nodeName = $this->nodeNamePolicy->getSafeNodeName($source);
        }
        if (mb_strlen($nodeName) > 250) {
            throw new \InvalidArgumentException(sprintf('Node name "%s" is too long.', $nodeName));
        }
        $node->setNodeName($nodeName);

        $manager->persist($source);
        $manager->persist($node);

        return $node;
    }

    public function createWithUrlAlias(
        string $urlAlias,
        string $title,
        ?NodeTypeInterface $type = null,
        ?TranslationInterface $translation = null,
        ?Node $node = null,
        ?Node $parent = null
    ): Node {
        $node = $this->create($title, $type, $translation, $node, $parent);
        /** @var UrlAliasRepository $repository */
        $repository = $this->managerRegistry->getRepository(UrlAlias::class);
        if (false === $repository->exists($urlAlias)) {
            $alias = new UrlAlias($node->getNodeSources()->first() ?: null);
            $alias->setAlias($urlAlias);
            $this->managerRegistry->getManagerForClass(UrlAlias::class)->persist($alias);
        }

        return $node;
    }
}
