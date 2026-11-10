<?php

declare(strict_types=1);

namespace App\Story;

use App\GeneratedEntity\NSAliasBlock;
use App\GeneratedEntity\NSArticleFeedBlock;
use App\GeneratedEntity\NSBasicBlock;
use App\GeneratedEntity\NSGroupBlock;
use App\GeneratedEntity\NSMenu;
use App\GeneratedEntity\NSMenuLink;
use App\GeneratedEntity\NSPage;

use function Zenstruck\Foundry\faker;

final class PageHierarchyStory extends AbstractNodeStory
{
    #[\Override]
    public function build(): void
    {
        $pages = [];
        $parentNode = null;
        for ($level = 1; $level <= 5; ++$level) {
            $page = $this->publishedNode('Page', NSPage::class, 'Page level '.$level, $parentNode, '-'.(6 - $level).' days');
            $page->getNode()->setNodeName('Page level '.$level);
            $page->getNode()->setHome(1 === $level);
            $page->setSubTitle(faker()->sentence(3));
            $page->setContent(faker()->paragraph(2));

            $parentNode = $page->getNode();
            $pages[] = $page;
        }

        $homePage = $pages[0];
        $homeNode = $homePage->getNode();

        $heroBlock = $this->publishedNode('BasicBlock', NSBasicBlock::class, 'Hero block', $homeNode, '-3 days');
        $heroBlock->setContent(faker()->paragraph(2));
        $heroBlock->setBooleanField(true);

        $groupBlock = $this->publishedNode('GroupBlock', NSGroupBlock::class, 'Feature group block', $homeNode, '-2 days');
        $groupChildBlock = $this->publishedNode('BasicBlock', NSBasicBlock::class, 'Feature child block', $groupBlock->getNode(), '-2 days');
        $groupChildBlock->setContent(faker()->paragraph(1));

        $articleFeedBlock = $this->publishedNode('ArticleFeedBlock', NSArticleFeedBlock::class, 'Latest news', $homeNode, '-1 day');
        $articleFeedBlock->setListingCount(6);

        $aliasBlock = $this->publishedNode('AliasBlock', NSAliasBlock::class, 'Hero alias block', $homeNode, '-1 day');

        $menu = $this->publishedNode('Menu', NSMenu::class, 'Main menu', null, '-1 day');
        $menuLinkHome = $this->publishedNode('MenuLink', NSMenuLink::class, 'Home link', $menu->getNode(), '-1 day');
        $menuLinkSection = $this->publishedNode('MenuLink', NSMenuLink::class, 'Section link', $menu->getNode(), '-1 day');
        $menuLinkExternal = $this->publishedNode('MenuLink', NSMenuLink::class, 'External link', $menu->getNode(), '-1 day');
        $menuLinkExternal->setLinkExternalUrl('https://example.org');

        $this->relate($homePage, $pages[1], 'node_references');
        $this->relate($aliasBlock, $heroBlock, 'block');
        $this->relate($menuLinkHome, $homePage, 'link_internal_reference');
        $this->relate($menuLinkSection, $pages[1], 'link_internal_reference');

        $this->entityManager->flush();

        $this->addState('homePage', $homePage);
        $this->addState('pageLevel2', $pages[1]);
    }
}
