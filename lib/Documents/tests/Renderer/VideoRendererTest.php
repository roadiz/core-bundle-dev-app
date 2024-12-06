<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Tests\Renderer;

use RZ\Roadiz\Documents\ArrayDocumentFinder;
use RZ\Roadiz\Documents\DocumentFinderInterface;
use RZ\Roadiz\Documents\Models\SimpleDocument;
use RZ\Roadiz\Documents\Renderer\VideoRenderer;

class VideoRendererTest extends AbstractRendererTestCase
{
    protected function getRenderer(): VideoRenderer
    {
        return new VideoRenderer(
            $this->getFilesystemOperator(),
            $this->getDocumentFinder(),
            $this->getEnvironment(),
            $this->getUrlGenerator()
        );
    }

    public function testSupports(): void
    {
        $mockValidDocument = new SimpleDocument();
        $mockValidDocument->setFilename('file.mp4');
        $mockValidDocument->setMimeType('video/mp4');

        $mockInvalidDocument = new SimpleDocument();
        $mockInvalidDocument->setFilename('file.jpg');
        $mockInvalidDocument->setMimeType('image/jpeg');

        $renderer = $this->getRenderer();

        $this->assertEquals(
            'video/mp4',
            $mockValidDocument->getMimeType()
        );
        $this->assertTrue(
            $renderer->supports($mockValidDocument, [])
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
        $mockDocument->setFilename('file.mp4');
        $mockDocument->setFolder('folder');
        $mockDocument->setMimeType('video/mp4');

        $mockDocument2 = new SimpleDocument();
        $mockDocument2->setFilename('file2.ogg');
        $mockDocument2->setFolder('folder');
        $mockDocument2->setMimeType('video/ogg');

        $renderer = $this->getRenderer();

        $this->assertEquals(
            'video/mp4',
            $mockDocument->getMimeType()
        );

        $this->assertHtmlTidyEquals(
            <<<EOT
<video controls>
    <source type="video/webm" src="/files/folder/file.webm">
    <source type="video/mp4" src="/files/folder/file.mp4">
    <p>Your browser does not support native video.</p>
</video>
EOT
            ,
            $renderer->render($mockDocument, [])
        );

        $this->assertHtmlTidyEquals(
            <<<EOT
<video controls>
    <source type="video/ogg" src="/files/folder/file2.ogg">
    <p>Your browser does not support native video.</p>
</video>
EOT
            ,
            $renderer->render($mockDocument2, [])
        );

        $this->assertHtmlTidyEquals(
            <<<EOT
<video controls autoplay playsinline loop>
    <source type="video/webm" src="/files/folder/file.webm">
    <source type="video/mp4" src="/files/folder/file.mp4">
    <p>Your browser does not support native video.</p>
</video>
EOT
            ,
            $renderer->render($mockDocument, [
                'controls' => true,
                'loop' => true,
                'autoplay' => true,
            ])
        );

        $this->assertHtmlTidyEquals(
            <<<EOT
<video controls autoplay playsinline muted loop>
    <source type="video/webm" src="/files/folder/file.webm">
    <source type="video/mp4" src="/files/folder/file.mp4">
    <p>Your browser does not support native video.</p>
</video>
EOT
            ,
            $renderer->render($mockDocument, [
                'controls' => true,
                'loop' => true,
                'autoplay' => true,
                'muted' => true,
            ])
        );

        $this->assertHtmlTidyEquals(
            <<<EOT
<video>
    <source type="video/webm" src="/files/folder/file.webm">
    <source type="video/mp4" src="/files/folder/file.mp4">
    <p>Your browser does not support native video.</p>
</video>
EOT
            ,
            $renderer->render($mockDocument, [
                'controls' => false,
            ])
        );
    }

    private function getDocumentFinder(): DocumentFinderInterface
    {
        $finder = new ArrayDocumentFinder();

        $finder->addDocument(
            (new SimpleDocument())
                ->setFilename('file.mp4')
                ->setFolder('folder')
                ->setMimeType('video/mp4')
        );
        $finder->addDocument(
            (new SimpleDocument())
                ->setFilename('file.webm')
                ->setFolder('folder')
                ->setMimeType('video/webm')
        );
        $finder->addDocument(
            (new SimpleDocument())
                ->setFilename('file2.ogg')
                ->setFolder('folder')
                ->setMimeType('video/ogg')
        );

        return $finder;
    }
}
