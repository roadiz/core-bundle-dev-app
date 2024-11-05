<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Tests\Renderer;

use RZ\Roadiz\Documents\Models\SimpleDocument;
use RZ\Roadiz\Documents\Renderer\PdfRenderer;

class PdfRendererTest extends AbstractRendererTestCase
{
    protected function getRenderer(): PdfRenderer
    {
        return new PdfRenderer(
            $this->getFilesystemOperator(),
            $this->getEnvironment(),
            $this->getUrlGenerator()
        );
    }

    public function testSupports(): void
    {
        $mockValidDocument = new SimpleDocument();
        $mockValidDocument->setFilename('file.pdf');
        $mockValidDocument->setMimeType('application/pdf');

        $mockInvalidDocument = new SimpleDocument();
        $mockInvalidDocument->setFilename('file.jpg');
        $mockInvalidDocument->setMimeType('image/jpeg');

        $renderer = $this->getRenderer();

        $this->assertEquals(
            'application/pdf',
            $mockValidDocument->getMimeType()
        );
        $this->assertTrue(
            $renderer->supports($mockValidDocument, [
                'embed' => true,
            ])
        );

        $this->assertEquals(
            'image/jpeg',
            $mockInvalidDocument->getMimeType()
        );
        $this->assertFalse(
            $renderer->supports($mockInvalidDocument, [])
        );
    }

    public function testRender(): void
    {
        $mockDocument = new SimpleDocument();
        $mockDocument->setFilename('file.pdf');
        $mockDocument->setFolder('folder');
        $mockDocument->setMimeType('application/pdf');

        $renderer = $this->getRenderer();

        $this->assertEquals(
            'application/pdf',
            $mockDocument->getMimeType()
        );
        $this->assertHtmlTidyEquals(
            '<object type="application/pdf" data="/files/folder/file.pdf"><p>Your browser does not support PDF native viewer.</p></object>',
            $renderer->render($mockDocument, [
                'embed' => true,
            ])
        );
    }
}
