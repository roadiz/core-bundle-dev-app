<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Tests\Renderer;

use RZ\Roadiz\Documents\Models\SimpleDocument;
use RZ\Roadiz\Documents\Renderer\InlineSvgRenderer;

class InlineSvgRendererTest extends AbstractRendererTestCase
{
    protected function getRenderer(): InlineSvgRenderer
    {
        return new InlineSvgRenderer(
            $this->getFilesystemOperator()
        );
    }

    public function testSupports(): void
    {
        $mockValidDocument = new SimpleDocument();
        $mockInvalidDocument = new SimpleDocument();

        $mockValidDocument->setFilename('file.svg');
        $mockValidDocument->setMimeType('image/svg');

        $mockInvalidDocument->setFilename('file.jpg');
        $mockInvalidDocument->setMimeType('image/jpeg');

        $renderer = $this->getRenderer();

        $this->assertEquals(
            'image/svg',
            $mockValidDocument->getMimeType()
        );

        $this->assertTrue($renderer->supports($mockValidDocument, ['inline' => true]));
        $this->assertFalse($renderer->supports($mockValidDocument, ['inline' => false]));

        $this->assertEquals(
            'image/jpeg',
            $mockInvalidDocument->getMimeType()
        );
        $this->assertFalse($renderer->supports($mockInvalidDocument, []));
    }

    public function testRender(): void
    {
        $mockDocument = new SimpleDocument();

        $mockDocument->setFilename('file.svg');
        $mockDocument->setFolder('folder');
        $mockDocument->setMimeType('image/svg');

        $renderer = $this->getRenderer();

        $this->assertEquals(
            'image/svg',
            $mockDocument->getMimeType()
        );

        $this->assertHtmlTidyEquals(
            <<<EOT
<svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="100" height="100">
    <rect width="50" height="50" x="25" y="25" fill="green"></rect>
</svg>
EOT,
            $renderer->render($mockDocument, ['inline' => true])
        );
    }
}
