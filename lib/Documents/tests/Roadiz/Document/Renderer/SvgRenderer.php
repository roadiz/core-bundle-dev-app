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
use RZ\Roadiz\Documents\Models\DocumentInterface;

class SvgRenderer extends atoum
{
    public function testSupports()
    {
        /** @var DocumentInterface $mockValidDocument */
        $mockValidDocument = new \mock\RZ\Roadiz\Documents\Models\SimpleDocument();
        /** @var DocumentInterface $mockInvalidDocument */
        $mockInvalidDocument = new \mock\RZ\Roadiz\Documents\Models\SimpleDocument();

        $mockValidDocument->setFilename('file.svg');
        $mockValidDocument->setMimeType('image/svg+xml');

        $mockInvalidDocument->setFilename('file.jpg');
        $mockInvalidDocument->setMimeType('image/jpeg');

        $this
            ->given($renderer = $this->newTestedInstance($this->getFilesystemOperator()))
            ->then
            ->string($mockValidDocument->getMimeType())
            ->isEqualTo('image/svg+xml')
            ->boolean($renderer->supports($mockValidDocument, []))
            ->isEqualTo(true)
            ->boolean($renderer->supports($mockValidDocument, ['inline' => false]))
            ->isEqualTo(true)
            ->boolean($renderer->supports($mockValidDocument, ['inline' => true]))
            ->isEqualTo(false)
            ->string($mockInvalidDocument->getMimeType())
            ->isEqualTo('image/jpeg')
            ->boolean($renderer->supports($mockInvalidDocument, []))
            ->isEqualTo(false);
    }

    public function testRender()
    {
        /** @var DocumentInterface $mockDocument */
        $mockDocument = new \mock\RZ\Roadiz\Documents\Models\SimpleDocument();

        $mockDocument->setFilename('file2.svg');
        $mockDocument->setFolder('folder');
        $mockDocument->setMimeType('image/svg+xml');

        $this
            ->given($renderer = $this->newTestedInstance($this->getFilesystemOperator()))
            ->then
            ->string($mockDocument->getMimeType())
            ->isEqualTo('image/svg+xml')
            ->string($renderer->render($mockDocument, []))
            ->isEqualTo(<<<EOT
<img src="/files/folder/file2.svg" />
EOT
);
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
}
