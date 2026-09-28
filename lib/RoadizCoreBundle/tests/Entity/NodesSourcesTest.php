<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\Translation;

class NodesSourcesTest extends TestCase
{
    private Translation $translation;

    #[\Override]
    protected function setUp(): void
    {
        $this->translation = (new Translation())->setLocale('en');
    }

    public function testReachableSourceIsItsOwnReachableParent(): void
    {
        $page = $this->createSource(true);

        $this->assertSame($page, $page->getFirstReachableParent());
    }

    public function testBlockReturnsItsPage(): void
    {
        $page = $this->createSource(true);
        $block = $this->createSource(false, $page);

        $this->assertSame($page, $block->getFirstReachableParent());
    }

    public function testNestedBlockReturnsItsPage(): void
    {
        $page = $this->createSource(true);
        $block = $this->createSource(false, $page);
        $nested = $this->createSource(false, $block);

        $this->assertSame($page, $nested->getFirstReachableParent());
    }

    public function testBlockWithoutReachableAncestorReturnsNull(): void
    {
        $menu = $this->createSource(false);
        $link = $this->createSource(false, $menu);

        $this->assertNull($link->getFirstReachableParent());
    }

    /*
     * isReachable() is generated per node type: stub it the way generated entities do.
     */
    private function createSource(bool $reachable, ?NodesSources $parent = null): NodesSources
    {
        $node = new Node();
        if (null !== $parent) {
            $node->setParent($parent->getNode());
        }

        if ($reachable) {
            return new class($node, $this->translation) extends NodesSources {
                #[\Override]
                public function isReachable(): bool
                {
                    return true;
                }
            };
        }

        return new class($node, $this->translation) extends NodesSources {
            #[\Override]
            public function isReachable(): bool
            {
                return false;
            }
        };
    }
}
