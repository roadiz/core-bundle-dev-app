<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Tests\Renderer;

use RZ\Roadiz\Documents\Models\DocumentInterface;
use RZ\Roadiz\Documents\Models\SimpleDocument;
use RZ\Roadiz\Documents\Renderer\SvgRenderer;

class SvgRendererTest extends AbstractRendererTestCase
{
    protected function getRenderer(): SvgRenderer
    {
        return new SvgRenderer(
            $this->getFilesystemOperator()
        );
    }

    public function testSupports(): void
    {
        $mockValidDocument = new SimpleDocument();
        $mockInvalidDocument = new SimpleDocument();

        $mockValidDocument->setFilename('file.svg');
        $mockValidDocument->setMimeType('image/svg+xml');

        $mockInvalidDocument->setFilename('file.jpg');
        $mockInvalidDocument->setMimeType('image/jpeg');

        $renderer = $this->getRenderer();

        $this->assertIsString($mockValidDocument->getMimeType());
        $this->assertEquals(
            'image/svg+xml',
            $mockValidDocument->getMimeType()
        );

        $this->assertTrue(
            $renderer->supports($mockValidDocument, [])
        );
        $this->assertTrue(
            $renderer->supports($mockValidDocument, ['inline' => false])
        );
        $this->assertFalse(
            $renderer->supports($mockValidDocument, ['inline' => true])
        );
        $this->assertFalse(
            $renderer->supports($mockInvalidDocument, [])
        );
    }

    public function testRender(): void
    {
        /** @var DocumentInterface $mockDocument */
        $mockDocument = new SimpleDocument();

        $mockDocument->setFilename('file2.svg');
        $mockDocument->setFolder('folder');
        $mockDocument->setMimeType('image/svg+xml');

        $renderer = $this->getRenderer();
        $this->assertEquals(
            'image/svg+xml',
            $mockDocument->getMimeType()
        );
        $this->assertHtmlTidyEquals(
            <<<EOT
<img src="/files/folder/file2.svg" />
EOT,
            $renderer->render($mockDocument, [])
        );
    }
}
