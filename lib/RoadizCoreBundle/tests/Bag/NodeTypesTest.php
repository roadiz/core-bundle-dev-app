<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Tests\Bag;

use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Node-types compiled by VarExporter must round-trip with their fields and back-references.
 */
final class NodeTypesTest extends KernelTestCase
{
    public function testWarmUpExportsNodeTypes(): void
    {
        self::bootKernel();
        $cacheDir = sys_get_temp_dir().'/'.uniqid('node_types_', true);

        try {
            $nodeTypes = static::getContainer()->get(NodeTypes::class);
            $this->assertInstanceOf(NodeTypes::class, $nodeTypes);
            $files = $nodeTypes->warmUp($cacheDir);
            $this->assertSame([$cacheDir.'/roadiz/node_types.php'], $files);

            $parameters = require $files[0];
            $article = $parameters['Article'] ?? null;
            $this->assertInstanceOf(NodeType::class, $article);
            $this->assertSame($article, $parameters[$article->getSourceEntityFullQualifiedClassName()]);
            $this->assertSame($article, $article->getFieldByName('content')?->getNodeType());
        } finally {
            (new Filesystem())->remove($cacheDir);
        }
    }
}
