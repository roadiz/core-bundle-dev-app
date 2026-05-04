<?php

declare(strict_types=1);

namespace App\Story;

use App\GeneratedEntity\NSAliasBlock;
use App\GeneratedEntity\NSArticleFeedBlock;
use App\GeneratedEntity\NSBasicBlock;
use App\GeneratedEntity\NSMenuLink;
use App\GeneratedEntity\NSPage;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodesToNodes;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Enum\NodeStatus;
use RZ\Roadiz\CoreBundle\Node\UniqueNodeGenerator;
use Zenstruck\Foundry\Story;

use function Zenstruck\Foundry\faker;

final class PageHierarchyStory extends Story
{
    public function __construct(
        private readonly UniqueNodeGenerator $uniqueNodeGenerator,
        private readonly NodeTypes $nodeTypesBag,
        private readonly ManagerRegistry $managerRegistry,
    ) {
    }

    #[\Override]
    public function build(): void
    {
        $translation = TranslationsStory::get('defaultTranslation');
        if (!$translation instanceof Translation) {
            throw new \RuntimeException('Default translation story state is invalid.');
        }

        $manager = $this->managerRegistry->getManagerForClass(Node::class);
        if (null === $manager) {
            throw new \RuntimeException('No entity manager found for Node class.');
        }

        $pageType = $this->getNodeType('Page');
        $basicBlockType = $this->getNodeType('BasicBlock');
        $groupBlockType = $this->getNodeType('GroupBlock');
        $articleFeedBlockType = $this->getNodeType('ArticleFeedBlock');
        $aliasBlockType = $this->getNodeType('AliasBlock');
        $menuType = $this->getNodeType('Menu');
        $menuLinkType = $this->getNodeType('MenuLink');

        $pages = [];
        $parentNode = null;

        for ($level = 1; $level <= 5; ++$level) {
            $page = $this->uniqueNodeGenerator->generate(
                nodeType: $pageType,
                translation: $translation,
                parent: $parentNode,
                flush: false,
            );
            $page->setTitle('Page level '.$level);
            $page->setPublishedAt(new \DateTime('-'.(6 - $level).' days'));
            $page->getNode()->setNodeName('Page level '.$level);
            $page->getNode()->setStatus(NodeStatus::PUBLISHED);

            if ($page instanceof NSPage) {
                $page->setSubTitle(faker()->sentence(3));
                $page->setContent(faker()->paragraph(2));
            }

            if (1 === $level) {
                $page->getNode()->setHome(true);
            }

            $parentNode = $page->getNode();
            $pages[] = $page;
            $this->addToPool('pages', $page);
            $this->addState('pageLevel'.$level, $page);
        }

        $homePage = $pages[0];
        $homeNode = $homePage->getNode();

        $heroBlock = $this->uniqueNodeGenerator->generate(
            nodeType: $basicBlockType,
            translation: $translation,
            parent: $homeNode,
            flush: false,
        );
        $heroBlock->setTitle('Hero block');
        $heroBlock->setPublishedAt(new \DateTime('-3 days'));
        $heroBlock->getNode()->setStatus(NodeStatus::PUBLISHED);
        if ($heroBlock instanceof NSBasicBlock) {
            $heroBlock->setContent(faker()->paragraph(2));
            $heroBlock->setBooleanField(true);
        }

        $groupBlock = $this->uniqueNodeGenerator->generate(
            nodeType: $groupBlockType,
            translation: $translation,
            parent: $homeNode,
            flush: false,
        );
        $groupBlock->setTitle('Feature group block');
        $groupBlock->setPublishedAt(new \DateTime('-2 days'));
        $groupBlock->getNode()->setStatus(NodeStatus::PUBLISHED);

        $groupChildBlock = $this->uniqueNodeGenerator->generate(
            nodeType: $basicBlockType,
            translation: $translation,
            parent: $groupBlock->getNode(),
            flush: false,
        );
        $groupChildBlock->setTitle('Feature child block');
        $groupChildBlock->setPublishedAt(new \DateTime('-2 days'));
        $groupChildBlock->getNode()->setStatus(NodeStatus::PUBLISHED);
        if ($groupChildBlock instanceof NSBasicBlock) {
            $groupChildBlock->setContent(faker()->paragraph(1));
        }

        $articleFeedBlock = $this->uniqueNodeGenerator->generate(
            nodeType: $articleFeedBlockType,
            translation: $translation,
            parent: $homeNode,
            flush: false,
        );
        $articleFeedBlock->setTitle('Latest news');
        $articleFeedBlock->setPublishedAt(new \DateTime('-1 day'));
        $articleFeedBlock->getNode()->setStatus(NodeStatus::PUBLISHED);
        if ($articleFeedBlock instanceof NSArticleFeedBlock) {
            $articleFeedBlock->setListingCount(6);
        }

        $aliasBlock = $this->uniqueNodeGenerator->generate(
            nodeType: $aliasBlockType,
            translation: $translation,
            parent: $homeNode,
            flush: false,
        );
        $aliasBlock->setTitle('Hero alias block');
        $aliasBlock->setPublishedAt(new \DateTime('-1 day'));
        $aliasBlock->getNode()->setStatus(NodeStatus::PUBLISHED);

        $menu = $this->uniqueNodeGenerator->generate(
            nodeType: $menuType,
            translation: $translation,
            flush: false,
        );
        $menu->setTitle('Main menu');
        $menu->setPublishedAt(new \DateTime('-1 day'));
        $menu->getNode()->setStatus(NodeStatus::PUBLISHED);

        $menuLinkHome = $this->uniqueNodeGenerator->generate(
            nodeType: $menuLinkType,
            translation: $translation,
            parent: $menu->getNode(),
            flush: false,
        );
        $menuLinkHome->setTitle('Home link');
        $menuLinkHome->setPublishedAt(new \DateTime('-1 day'));
        $menuLinkHome->getNode()->setStatus(NodeStatus::PUBLISHED);

        $menuLinkSection = $this->uniqueNodeGenerator->generate(
            nodeType: $menuLinkType,
            translation: $translation,
            parent: $menu->getNode(),
            flush: false,
        );
        $menuLinkSection->setTitle('Section link');
        $menuLinkSection->setPublishedAt(new \DateTime('-1 day'));
        $menuLinkSection->getNode()->setStatus(NodeStatus::PUBLISHED);

        $menuLinkExternal = $this->uniqueNodeGenerator->generate(
            nodeType: $menuLinkType,
            translation: $translation,
            parent: $menu->getNode(),
            flush: false,
        );
        $menuLinkExternal->setTitle('External link');
        $menuLinkExternal->setPublishedAt(new \DateTime('-1 day'));
        $menuLinkExternal->getNode()->setStatus(NodeStatus::PUBLISHED);
        if ($menuLinkExternal instanceof NSMenuLink) {
            $menuLinkExternal->setLinkExternalUrl('https://example.org');
        }

        $homeReferenceRelation = (new NodesToNodes($homeNode, $pages[1]->getNode()))
            ->setFieldName('node_references')
            ->setPosition(1);
        $manager->persist($homeReferenceRelation);

        $aliasRelation = (new NodesToNodes($aliasBlock->getNode(), $heroBlock->getNode()))
            ->setFieldName('block')
            ->setPosition(1);
        $manager->persist($aliasRelation);

        $menuHomeRelation = (new NodesToNodes($menuLinkHome->getNode(), $homeNode))
            ->setFieldName('link_internal_reference')
            ->setPosition(1);
        $manager->persist($menuHomeRelation);

        $menuSectionRelation = (new NodesToNodes($menuLinkSection->getNode(), $pages[1]->getNode()))
            ->setFieldName('link_internal_reference')
            ->setPosition(1);
        $manager->persist($menuSectionRelation);

        $manager->flush();

        $this->addState('homePage', $homePage);
        $this->addState('heroBlock', $heroBlock);
        $this->addState('groupBlock', $groupBlock);
        $this->addState('articleFeedBlock', $articleFeedBlock);
        $this->addState('aliasBlock', $aliasBlock);
        $this->addState('mainMenu', $menu);
    }

    private function getNodeType(string $name): NodeType
    {
        return $this->nodeTypesBag->get($name) ?? throw new \RuntimeException(sprintf('%s node type is missing.', $name));
    }
}
