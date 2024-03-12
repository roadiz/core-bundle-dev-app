<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Test\Renderer;

use RZ\Roadiz\Documents\Models\SimpleDocument;
use RZ\Roadiz\Documents\Renderer;

class ChainRendererTest extends AbstractRendererTestCase
{
    protected function getRenderer(): Renderer\ChainRenderer
    {
        return new Renderer\ChainRenderer([
            new Renderer\InlineSvgRenderer($this->getFilesystemOperator()),
            new Renderer\SvgRenderer($this->getFilesystemOperator()),
            new Renderer\PdfRenderer($this->getFilesystemOperator(), $this->getEnvironment(), $this->getUrlGenerator()),
            new Renderer\ImageRenderer($this->getFilesystemOperator(), $this->getEmbedFinderFactory(), $this->getEnvironment(), $this->getUrlGenerator()),
            new Renderer\PictureRenderer($this->getFilesystemOperator(), $this->getEmbedFinderFactory(), $this->getEnvironment(), $this->getUrlGenerator()),
            new Renderer\EmbedRenderer($this->getEmbedFinderFactory()),
        ]);
    }

    public function testRender(): void
    {
        $mockSvgDocument = new SimpleDocument();
        $mockSvgDocument->setFilename('file.svg');
        $mockSvgDocument->setFolder('folder');
        $mockSvgDocument->setMimeType('image/svg');

        $mockPdfDocument = new SimpleDocument();
        $mockPdfDocument->setFilename('file.pdf');
        $mockPdfDocument->setFolder('folder');
        $mockPdfDocument->setMimeType('application/pdf');

        $mockDocumentYoutube = new SimpleDocument();
        $mockDocumentYoutube->setFilename('poster.jpg');
        $mockDocumentYoutube->setEmbedId('xxxxxxx');
        $mockDocumentYoutube->setEmbedPlatform('youtube');
        $mockDocumentYoutube->setMimeType('image/jpeg');

        $mockPictureDocument = new SimpleDocument();
        $mockPictureDocument->setFilename('file.jpg');
        $mockPictureDocument->setFolder('folder');
        $mockPictureDocument->setMimeType('image/jpeg');

        $renderer = $this->getRenderer();

        $this->assertHtmlTidyEquals(
            '<object type="application/pdf" data="/files/folder/file.pdf"><p>Your browser does not support PDF native viewer.</p></object>',
            ($renderer->render($mockPdfDocument, [
                'embed' => true
            ]))
        );

        $this->assertHtmlTidyEquals(
            '<object type="application/pdf" data="http://dummy.test/files/folder/file.pdf"><p>Your browser does not support PDF native viewer.</p></object>',
            ($renderer->render($mockPdfDocument, ['absolute' => true, 'embed' => true]))
        );

        $this->assertHtmlTidyEquals(
            '<img src="/files/folder/file.svg" />',
            ($renderer->render($mockSvgDocument, []))
        );

        $this->assertHtmlTidyEquals(
            (<<<EOT
<svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="100" height="100">
    <rect width="50" height="50" x="25" y="25" fill="green"></rect>
</svg>
EOT
            ),
            ($renderer->render($mockSvgDocument, ['inline' => true]))
        );

        $this->assertIsBool($mockDocumentYoutube->isEmbed());
        $this->assertTrue($mockDocumentYoutube->isEmbed());

        $this->assertHtmlTidyEquals(
            (<<<EOT
<iframe src="https://www.youtube-nocookie.com/embed/xxxxxxx?rel=0&html5=1&wmode=transparent&loop=0&controls=1&fs=1&modestbranding=1&showinfo=0&enablejsapi=1&mute=0"
        allow="accelerometer; encrypted-media; gyroscope; picture-in-picture; fullscreen"
        allowFullScreen></iframe>
EOT
            ),
            ($renderer->render($mockDocumentYoutube, ['embed' => true]))
        );

        $this->assertHtmlTidyEquals(
            (<<<EOT
<picture>
<source type="image/webp" srcset="/assets/w300-q90/folder/file.jpg.webp">
<source type="image/jpeg" srcset="/assets/w300-q90/folder/file.jpg">
<img alt="file.jpg" src="/assets/w300-q90/folder/file.jpg" width="300" />
</picture>
EOT
            ),
            ($renderer->render($mockPictureDocument, [
                'width' => 300,
                'picture' => true
            ]))
        );
    }
}
