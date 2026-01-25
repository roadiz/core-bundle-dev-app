<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Node;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Contracts\NodeType\NodeTypeClassLocatorInterface;
use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Entity\UrlAlias;
use RZ\Roadiz\CoreBundle\Repository\AllStatusesNodeRepository;
use RZ\Roadiz\CoreBundle\Repository\UrlAliasRepository;

final readonly class NodeFactory
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
        private NodeNamePolicyInterface $nodeNamePolicy,
        private AllStatusesNodeRepository $allStatusesNodeRepository,
        private UrlAliasRepository $urlAliasRepository,
        private NodeTypes $nodeTypesBag,
        private NodeTypeClassLocatorInterface $nodeTypeClassLocator,
    ) {
    }

    public function create(
        string $title,
        ?NodeTypeInterface $type = null,
        ?TranslationInterface $translation = null,
        ?Node $node = null,
        ?Node $parent = null,
    ): Node {
        if (null === $node && null === $type) {
            throw new \RuntimeException('Cannot create node from null NodeType and null Node.');
        }

        if (null === $translation) {
            $translation = $this->managerRegistry->getRepository(Translation::class)->findDefault();
        }

        if (null === $node) {
            $node = new Node();
            $node->setNodeTypeName($type->getName());
        }

        $nodeType = $this->nodeTypesBag->get($node->getNodeTypeName());
        if (!$nodeType instanceof NodeType) {
            throw new \RuntimeException('Cannot create node from invalid NodeType.');
        }
        $node->setTtl($nodeType->getDefaultTTL());

        if (null !== $parent) {
            $node->setParent($parent);
        }

        $sourceClass = $this->nodeTypeClassLocator->getSourceEntityFullQualifiedClassName($nodeType);
        /** @var NodesSources $source */
        $source = new $sourceClass($node, $translation);
        $manager = $this->managerRegistry->getManagerForClass(NodesSources::class) ?? throw new \RuntimeException('No entity manager found for NodesSources class.');
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
        if (true === $this->allStatusesNodeRepository->exists($nodeName)) {
            $nodeName = $this->nodeNamePolicy->getSafeNodeName($source);
        }
        if (\mb_strlen($nodeName) > 250) {
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
        ?Node $parent = null,
    ): Node {
        $node = $this->create($title, $type, $translation, $node, $parent);
        $nodeSource = $node->getNodeSources()->first();
        if (false !== $nodeSource && false === $this->urlAliasRepository->exists($urlAlias)) {
            $alias = new UrlAlias();
            $alias->setNodeSource($nodeSource);
            $alias->setAlias($urlAlias);
            $this->managerRegistry->getManagerForClass(UrlAlias::class)?->persist($alias);
        }

        return $node;
    }
}
