<?php
declare(strict_types=1);

namespace RZ\Roadiz\Documents\Renderer\tests\units;

use atoum;
use League\Flysystem\Config;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\MountManager;
use League\Flysystem\UrlGeneration\PublicUrlGenerator;
use RZ\Roadiz\Documents\MediaFinders\EmbedFinderFactory;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use RZ\Roadiz\Documents\Renderer;
use RZ\Roadiz\Documents\UrlGenerators\DocumentUrlGeneratorInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class ChainRenderer extends atoum
{
    public function testRender()
    {
        /** @var DocumentInterface $mockSvgDocument */
        $mockSvgDocument = new \mock\RZ\Roadiz\Documents\Models\SimpleDocument();
        $mockSvgDocument->setFilename('file.svg');
        $mockSvgDocument->setFolder('folder');
        $mockSvgDocument->setMimeType('image/svg');

        /** @var DocumentInterface $mockPdfDocument */
        $mockPdfDocument = new \mock\RZ\Roadiz\Documents\Models\SimpleDocument();
        $mockPdfDocument->setFilename('file.pdf');
        $mockPdfDocument->setFolder('folder');
        $mockPdfDocument->setMimeType('application/pdf');

        /** @var DocumentInterface $mockDocumentYoutube */
        $mockDocumentYoutube = new \mock\RZ\Roadiz\Documents\Models\SimpleDocument();
        $mockDocumentYoutube->setFilename('poster.jpg');
        $mockDocumentYoutube->setEmbedId('xxxxxxx');
        $mockDocumentYoutube->setEmbedPlatform('youtube');
        $mockDocumentYoutube->setMimeType('image/jpeg');

        /** @var DocumentInterface $mockPictureDocument */
        $mockPictureDocument = new \mock\RZ\Roadiz\Documents\Models\SimpleDocument();
        $mockPictureDocument->setFilename('file.jpg');
        $mockPictureDocument->setFolder('folder');
        $mockPictureDocument->setMimeType('image/jpeg');

        $renderers = [
            new Renderer\InlineSvgRenderer($this->getFilesystemOperator()),
            new Renderer\SvgRenderer($this->getFilesystemOperator()),
            new Renderer\PdfRenderer($this->getFilesystemOperator(), $this->getEnvironment(), $this->getUrlGenerator()),
            new Renderer\ImageRenderer($this->getFilesystemOperator(), $this->getEmbedFinderFactory(), $this->getEnvironment(), $this->getUrlGenerator()),
            new Renderer\PictureRenderer($this->getFilesystemOperator(), $this->getEmbedFinderFactory(), $this->getEnvironment(), $this->getUrlGenerator()),
            new Renderer\EmbedRenderer($this->getEmbedFinderFactory()),
        ];

        $this
            ->given($renderer = $this->newTestedInstance($renderers))
            ->then
            ->string($this->htmlTidy($renderer->render($mockPdfDocument, [
                'embed' => true
            ])))
            ->isEqualTo('<object type="application/pdf" data="/files/folder/file.pdf"><p>Your browser does not support PDF native viewer.</p></object>')
            ->string($this->htmlTidy($renderer->render($mockPdfDocument, ['absolute' => true, 'embed' => true])))
            ->isEqualTo('<object type="application/pdf" data="http://dummy.test/files/folder/file.pdf"><p>Your browser does not support PDF native viewer.</p></object>')
            ->string($this->htmlTidy($renderer->render($mockSvgDocument, [])))
            ->isEqualTo($this->htmlTidy(<<<EOT
<img src="/files/folder/file.svg" />
EOT
            ))
            ->string($this->htmlTidy($renderer->render($mockSvgDocument, ['inline' => true])))
            ->isEqualTo($this->htmlTidy(<<<EOT
<svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="100" height="100">
    <rect width="50" height="50" x="25" y="25" fill="green"></rect>
</svg>
EOT
            ))
            ->boolean($mockDocumentYoutube->isEmbed())
            ->isEqualTo(true)
            ->string($this->htmlTidy($renderer->render($mockDocumentYoutube, ['embed' => true])))
            ->isEqualTo($this->htmlTidy(<<<EOT
<iframe src="https://www.youtube-nocookie.com/embed/xxxxxxx?rel=0&html5=1&wmode=transparent&loop=0&controls=1&fs=1&modestbranding=1&showinfo=0&enablejsapi=1&mute=0"
        allow="accelerometer; encrypted-media; gyroscope; picture-in-picture; fullscreen"
        allowFullScreen></iframe>
EOT
            ))
            ->string($this->htmlTidy($renderer->render($mockPictureDocument, [
                'width' => 300,
                'picture' => true
            ])))
            ->isEqualTo($this->htmlTidy(<<<EOT
<picture>
<source type="image/webp" srcset="/assets/w300-q90/folder/file.jpg.webp">
<source type="image/jpeg" srcset="/assets/w300-q90/folder/file.jpg">
<img alt="" aria-hidden="true" src="/assets/w300-q90/folder/file.jpg" width="300" />
</picture>
EOT
            ))
        ;
    }

    private function htmlTidy(string $body): string
    {
        $body = preg_replace('#[\n\r\t\s]{2,}#', ' ', $body);
        $body = str_replace("&#x2F;", '/', $body);
        $body = html_entity_decode($body);
        return preg_replace('#\>[\n\r\t\s]+\<#', '><', $body);
    }

    /**
     * @return DocumentUrlGeneratorInterface
     */
    private function getUrlGenerator(): DocumentUrlGeneratorInterface
    {
        return new \mock\RZ\Roadiz\Documents\UrlGenerators\DummyDocumentUrlGenerator();
    }

    private function getFilesystemOperator(): FilesystemOperator
    {
        return new MountManager([
            'public' => new Filesystem(
                new LocalFilesystemAdapter(dirname(__DIR__) . '/../../../files/'),
                publicUrlGenerator: new class() implements PublicUrlGenerator
                {
                    public function publicUrl(string $path, Config $config): string
                    {
                        return '/files/' . $path;
                    }
                }
            ),
            'private' => new Filesystem(
                new LocalFilesystemAdapter(dirname(__DIR__) . '/../../../files/'),
                publicUrlGenerator: new class() implements PublicUrlGenerator
                {
                    public function publicUrl(string $path, Config $config): string
                    {
                        return '/files/' . $path;
                    }
                }
            )
        ]);
    }

    private function getEnvironment(): Environment
    {
        $loader = new FilesystemLoader([
            dirname(__DIR__) . '/../../../src/Resources/views'
        ]);
        return new Environment($loader, [
            'autoescape' => false
        ]);
    }

    /**
     * @return EmbedFinderFactory
     */
    private function getEmbedFinderFactory(): EmbedFinderFactory
    {
        return new EmbedFinderFactory([
            'youtube' => \mock\RZ\Roadiz\Documents\MediaFinders\AbstractYoutubeEmbedFinder::class,
            'vimeo' => \mock\RZ\Roadiz\Documents\MediaFinders\AbstractVimeoEmbedFinder::class,
            'dailymotion' => \mock\RZ\Roadiz\Documents\MediaFinders\AbstractDailymotionEmbedFinder::class,
            'soundcloud' => \mock\RZ\Roadiz\Documents\MediaFinders\AbstractSoundcloudEmbedFinder::class,
        ]);
    }
}
