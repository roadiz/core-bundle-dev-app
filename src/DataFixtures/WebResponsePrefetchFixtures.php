<?php

declare(strict_types=1);

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodesSourcesDocuments;
use RZ\Roadiz\CoreBundle\Entity\NodesToNodes;
use RZ\Roadiz\CoreBundle\Entity\Realm;
use RZ\Roadiz\CoreBundle\Entity\RealmNode;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Entity\UrlAlias;
use RZ\Roadiz\CoreBundle\Enum\NodeStatus;
use RZ\Roadiz\CoreBundle\Model\RealmInterface;
use RZ\Roadiz\CoreBundle\Node\UniqueNodeGenerator;

/**
 * Pages with media, nodes references, URL aliases and nested blocks, used by WebResponsePrefetchTest.
 */
class WebResponsePrefetchFixtures extends Fixture implements DependentFixtureInterface
{
    public const string DENIED_ROLE = 'ROLE_PREFETCH_NEVER_GRANTED';

    private Translation $translation;

    public function __construct(
        private readonly UniqueNodeGenerator $uniqueNodeGenerator,
        private readonly NodeTypes $nodeTypesBag,
    ) {
    }

    #[\Override]
    public function getDependencies(): array
    {
        return [
            AppFixtures::class,
        ];
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $this->translation = $this->getReference(AppFixtures::DEFAULT_TRANSLATION_REFERENCE, Translation::class);

        $first = $this->createDocument($manager, 'prefetch_first.jpg');
        $second = $this->createDocument($manager, 'prefetch_second.jpg');

        $target = $this->createSource($manager, 'Page', 'prefetch-target');
        $this->addDocuments($manager, $target, 'images', [$first]);
        $manager->persist((new UrlAlias())->setAlias('prefetch-alias')->setNodeSource($target));

        $unpublished = $this->createSource($manager, 'Page', 'prefetch-unpublished');
        $unpublished->getNode()->setStatus(NodeStatus::DRAFT);

        $denied = $this->createSource($manager, 'Page', 'prefetch-denied');
        $this->addRealm($manager, $denied, 'prefetch-deny-realm', RealmInterface::BEHAVIOUR_DENY);

        foreach (['prefetch-page' => 1, 'prefetch-page-more' => 3] as $nodeName => $blockCount) {
            $page = $this->createSource($manager, 'Page', $nodeName);
            $this->addDocuments($manager, $page, 'header_image', [$second, $first]);
            $this->addReferences($manager, $page, 'node_references', [$target, $unpublished, $denied]);

            $group = $this->createSource($manager, 'GroupBlock', $nodeName.'-group', $page->getNode(), $blockCount + 1);
            for ($i = 0; $i < $blockCount; ++$i) {
                $block = $this->createSource($manager, 'BasicBlock', $nodeName.'-block-'.$i, $page->getNode(), $i + 1);
                $this->addDocuments($manager, $block, 'image', [$second, $first]);

                $nestedBlock = $this->createSource($manager, 'BasicBlock', $nodeName.'-nested-block-'.$i, $group->getNode(), $i + 1);
                $this->addDocuments($manager, $nestedBlock, 'image', [$first]);
            }
        }

        foreach (['prefetch-hidden' => 1, 'prefetch-hidden-more' => 3] as $nodeName => $blockCount) {
            $page = $this->createSource($manager, 'Page', $nodeName);
            $this->addRealm($manager, $page, $nodeName.'-realm', RealmInterface::BEHAVIOUR_HIDE_BLOCKS);
            for ($i = 0; $i < $blockCount; ++$i) {
                $block = $this->createSource($manager, 'BasicBlock', $nodeName.'-block-'.$i, $page->getNode(), $i + 1);
                $this->addDocuments($manager, $block, 'image', [$first]);
            }
        }

        // Common content menu: a reachable walker item with an URL alias, and links referencing the same node.
        $menu = $this->createSource($manager, 'Menu', 'prefetch-menu');
        $menuPage = $this->createSource($manager, 'Page', 'prefetch-menu-page', $menu->getNode(), 1);
        $manager->persist((new UrlAlias())->setAlias('prefetch-menu-alias')->setNodeSource($menuPage));
        for ($i = 0; $i < 2; ++$i) {
            $link = $this->createSource($manager, 'MenuLink', 'prefetch-menu-link-'.$i, $menu->getNode(), $i + 2);
            $this->addReferences($manager, $link, 'link_internal_reference', [$target, $unpublished]);
        }

        $manager->flush();
    }

    /**
     * @param RealmInterface::BEHAVIOUR_* $behaviour
     */
    private function addRealm(ObjectManager $manager, NodesSources $source, string $name, string $behaviour): void
    {
        $realm = (new Realm())
            ->setName($name)
            ->setType(RealmInterface::TYPE_ROLE)
            ->setBehaviour($behaviour)
            ->setRole(self::DENIED_ROLE);
        $manager->persist($realm);
        $manager->persist(
            (new RealmNode())
                ->setNode($source->getNode())
                ->setRealm($realm)
                ->setInheritanceType(RealmInterface::INHERITANCE_ROOT)
        );
    }

    private function createSource(ObjectManager $manager, string $nodeTypeName, string $nodeName, ?Node $parent = null, int $position = 1): NodesSources
    {
        $source = $this->uniqueNodeGenerator->generate(
            // @phpstan-ignore-next-line
            nodeType: $this->nodeTypesBag->get($nodeTypeName),
            translation: $this->translation,
            parent: $parent,
            flush: false,
        );
        $source->setTitle($nodeName);
        $source->setPublishedAt(new \DateTime('2020-01-01 00:00:00'));
        $source->getNode()
            ->setNodeName($nodeName)
            ->setVisible(true)
            ->setStatus(NodeStatus::PUBLISHED)
            ->setPosition($position);

        return $source;
    }

    private function createDocument(ObjectManager $manager, string $filename): Document
    {
        $document = (new Document())
            ->setFilename($filename)
            ->setMimeType('image/jpeg')
            ->setFolder('fixtures');
        $manager->persist($document);

        return $document;
    }

    /**
     * @param array<Document> $documents
     */
    private function addDocuments(ObjectManager $manager, NodesSources $source, string $fieldName, array $documents): void
    {
        foreach (array_values($documents) as $position => $document) {
            $manager->persist(
                (new NodesSourcesDocuments($source, $document))
                    ->setFieldName($fieldName)
                    ->setPosition($position + 1)
            );
        }
    }

    /**
     * @param array<NodesSources> $references
     */
    private function addReferences(ObjectManager $manager, NodesSources $source, string $fieldName, array $references): void
    {
        foreach (array_values($references) as $position => $reference) {
            $manager->persist(
                (new NodesToNodes($source->getNode(), $reference->getNode()))
                    ->setFieldName($fieldName)
                    ->setPosition($position + 1)
            );
        }
    }
}
