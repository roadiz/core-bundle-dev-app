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
use RZ\Roadiz\Documents\ArrayDocumentFinder;
use RZ\Roadiz\Documents\DocumentFinderInterface;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use RZ\Roadiz\Documents\UrlGenerators\DocumentUrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class AudioRenderer extends atoum
{
    public function testSupports()
    {
        /** @var DocumentInterface $mockValidDocument */
        $mockValidDocument = new \mock\RZ\Roadiz\Documents\Models\SimpleDocument();
        $mockValidDocument->setFilename('file.mp3');
        $mockValidDocument->setMimeType('audio/mpeg');

        /** @var DocumentInterface $mockInvalidDocument */
        $mockInvalidDocument = new \mock\RZ\Roadiz\Documents\Models\SimpleDocument();
        $mockInvalidDocument->setFilename('file.jpg');
        $mockInvalidDocument->setMimeType('image/jpeg');

        $this
            ->given($renderer = $this->newTestedInstance(
                $this->getFilesystemOperator(),
                $this->getDocumentFinder(),
                $this->getEnvironment(),
                $this->getUrlGenerator()
            ))
            ->then
            ->string($mockValidDocument->getMimeType())
            ->isEqualTo('audio/mpeg')
            ->boolean($renderer->supports($mockValidDocument, []))
            ->isEqualTo(true)
            ->string($mockInvalidDocument->getMimeType())
            ->isEqualTo('image/jpeg')
            ->boolean($renderer->supports($mockInvalidDocument, []))
            ->isEqualTo(false);
    }

    public function testRender()
    {
        /** @var DocumentInterface $mockDocument */
        $mockDocument = new \mock\RZ\Roadiz\Documents\Models\SimpleDocument();
        $mockDocument->setFilename('file.mp3');
        $mockDocument->setFolder('folder');
        $mockDocument->setMimeType('audio/mpeg');

        /** @var DocumentInterface $mockDocument2 */
        $mockDocument2 = new \mock\RZ\Roadiz\Documents\Models\SimpleDocument();
        $mockDocument2->setFilename('file2.mp3');
        $mockDocument2->setFolder('folder');
        $mockDocument2->setMimeType('audio/mpeg');

        $this
            ->given($renderer = $this->newTestedInstance(
                $this->getFilesystemOperator(),
                $this->getDocumentFinder(),
                $this->getEnvironment(),
                $this->getUrlGenerator()
            ))
            ->then
            ->string($mockDocument->getMimeType())
            ->isEqualTo('audio/mpeg')
            ->string($this->htmlTidy($renderer->render($mockDocument, [])))
            ->isEqualTo($this->htmlTidy(<<<EOT
<audio controls>
    <source type="audio/ogg" src="/files/folder/file.ogg">
    <source type="audio/mpeg" src="/files/folder/file.mp3">
    <p>Your browser does not support native audio.</p>
</audio>
EOT
            ))
            ->string($this->htmlTidy($renderer->render($mockDocument2, [])))
            ->isEqualTo($this->htmlTidy(<<<EOT
<audio controls>
    <source type="audio/mpeg" src="/files/folder/file2.mp3">
    <p>Your browser does not support native audio.</p>
</audio>
EOT
            ))
            ->string($this->htmlTidy($renderer->render($mockDocument, [
                'controls' => true,
                'loop' => true,
                'autoplay' => true,
            ])))
            ->isEqualTo($this->htmlTidy(<<<EOT
<audio controls autoplay loop>
    <source type="audio/ogg" src="/files/folder/file.ogg">
    <source type="audio/mpeg" src="/files/folder/file.mp3">
    <p>Your browser does not support native audio.</p>
</audio>
EOT
            ))
            ->string($this->htmlTidy($renderer->render($mockDocument, [
                'controls' => false
            ])))
            ->isEqualTo($this->htmlTidy(<<<EOT
<audio>
    <source type="audio/ogg" src="/files/folder/file.ogg">
    <source type="audio/mpeg" src="/files/folder/file.mp3">
    <p>Your browser does not support native audio.</p>
</audio>
EOT
            ))
        ;
    }

    /**
     * @return DocumentFinderInterface
     */
    private function getDocumentFinder(): DocumentFinderInterface
    {
        $finder = new ArrayDocumentFinder();

        $finder->addDocument(
            (new \mock\RZ\Roadiz\Documents\Models\SimpleDocument())
                ->setFilename('file.mp3')
                ->setFolder('folder')
                ->setMimeType('audio/mpeg')
        );
        $finder->addDocument(
            (new \mock\RZ\Roadiz\Documents\Models\SimpleDocument())
                ->setFilename('file.ogg')
                ->setFolder('folder')
                ->setMimeType('audio/ogg')
        );
        $finder->addDocument(
            (new \mock\RZ\Roadiz\Documents\Models\SimpleDocument())
                ->setFilename('file2.mp3')
                ->setFolder('folder')
                ->setMimeType('audio/mpeg')
        );

        return $finder;
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
     * @param string $body
     *
     * @return string
     */
    private function htmlTidy(string $body): string
    {
        $body = preg_replace('#[\n\r\t\s]{2,}#', ' ', $body);
        $body = str_replace("&#x2F;", '/', $body);
        $body = html_entity_decode($body);
        return preg_replace('#\>[\n\r\t\s]+\<#', '><', $body);
    }
}
