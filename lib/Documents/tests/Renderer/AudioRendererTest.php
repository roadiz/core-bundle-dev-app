<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Tests\Renderer;

use RZ\Roadiz\Documents\ArrayDocumentFinder;
use RZ\Roadiz\Documents\DocumentFinderInterface;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use RZ\Roadiz\Documents\Models\SimpleDocument;
use RZ\Roadiz\Documents\Renderer\AudioRenderer;

class AudioRendererTest extends AbstractRendererTestCase
{
    protected function getRenderer(): AudioRenderer
    {
        return new AudioRenderer(
            $this->getFilesystemOperator(),
            $this->getDocumentFinder(),
            $this->getEnvironment(),
            $this->getUrlGenerator()
        );
    }

    public function testSupports(): void
    {
        /** @var DocumentInterface $mockValidDocument */
        $mockValidDocument = new SimpleDocument();
        $mockValidDocument->setFilename('file.mp3');
        $mockValidDocument->setMimeType('audio/mpeg');

        /** @var DocumentInterface $mockInvalidDocument */
        $mockInvalidDocument = new SimpleDocument();
        $mockInvalidDocument->setFilename('file.jpg');
        $mockInvalidDocument->setMimeType('image/jpeg');

        $renderer = $this->getRenderer();

        $this->assertIsString($mockValidDocument->getMimeType());
        $this->assertEquals('audio/mpeg', $mockValidDocument->getMimeType());
        $this->assertIsBool($renderer->supports($mockValidDocument, []));
        $this->assertTrue($renderer->supports($mockValidDocument, []));

        $this->assertIsString($mockInvalidDocument->getMimeType());
        $this->assertEquals('image/jpeg', $mockInvalidDocument->getMimeType());
        $this->assertIsBool($renderer->supports($mockInvalidDocument, []));
        $this->assertFalse($renderer->supports($mockInvalidDocument, []));
    }

    public function testRender(): void
    {
        /** @var DocumentInterface $mockDocument */
        $mockDocument = new SimpleDocument();
        $mockDocument->setFilename('file.mp3');
        $mockDocument->setFolder('folder');
        $mockDocument->setMimeType('audio/mpeg');

        /** @var DocumentInterface $mockDocument2 */
        $mockDocument2 = new SimpleDocument();
        $mockDocument2->setFilename('file2.mp3');
        $mockDocument2->setFolder('folder');
        $mockDocument2->setMimeType('audio/mpeg');


        $renderer = $this->getRenderer();
        $this->assertHtmlTidyEquals((<<<EOT
<audio controls>
    <source type="audio/ogg" src="/files/folder/file.ogg">
    <source type="audio/mpeg" src="/files/folder/file.mp3">
    <p>Your browser does not support native audio.</p>
</audio>
EOT
        ), ($renderer->render($mockDocument, [])));


        $this->assertHtmlTidyEquals((<<<EOT
<audio controls>
    <source type="audio/mpeg" src="/files/folder/file2.mp3">
    <p>Your browser does not support native audio.</p>
</audio>
EOT
        ), ($renderer->render($mockDocument2, [])));


        $this->assertHtmlTidyEquals((<<<EOT
<audio controls autoplay loop>
    <source type="audio/ogg" src="/files/folder/file.ogg">
    <source type="audio/mpeg" src="/files/folder/file.mp3">
    <p>Your browser does not support native audio.</p>
</audio>
EOT
        ), ($renderer->render($mockDocument, [
            'controls' => true,
            'loop' => true,
            'autoplay' => true,
        ])));


        $this->assertHtmlTidyEquals((<<<EOT
<audio>
    <source type="audio/ogg" src="/files/folder/file.ogg">
    <source type="audio/mpeg" src="/files/folder/file.mp3">
    <p>Your browser does not support native audio.</p>
</audio>
EOT
        ), ($renderer->render($mockDocument, [
            'controls' => false
        ])));
    }

    /**
     * @return DocumentFinderInterface
     */
    private function getDocumentFinder(): DocumentFinderInterface
    {
        $finder = new ArrayDocumentFinder();

        $finder->addDocument(
            (new SimpleDocument())
                ->setFilename('file.mp3')
                ->setFolder('folder')
                ->setMimeType('audio/mpeg')
        );
        $finder->addDocument(
            (new SimpleDocument())
                ->setFilename('file.ogg')
                ->setFolder('folder')
                ->setMimeType('audio/ogg')
        );
        $finder->addDocument(
            (new SimpleDocument())
                ->setFilename('file2.mp3')
                ->setFolder('folder')
                ->setMimeType('audio/mpeg')
        );

        return $finder;
    }
}
