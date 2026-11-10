<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Tests;

use League\Flysystem\MountManager;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use RZ\Roadiz\Documents\AbstractDocumentFactory;
use RZ\Roadiz\Documents\DocumentFinderInterface;
use RZ\Roadiz\Documents\Exceptions\DocumentTypeNotAllowedException;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use Symfony\Component\HttpFoundation\File\File;

final class AbstractDocumentFactoryTest extends TestCase
{
    private function createFactory(): AbstractDocumentFactory
    {
        return new class($this->createMock(MountManager::class), $this->createMock(DocumentFinderInterface::class), new NullLogger()) extends AbstractDocumentFactory {
            protected function createDocument(): DocumentInterface
            {
                throw new \BadMethodCallException('Not needed for this test.');
            }

            protected function persistDocument(DocumentInterface $document): void
            {
                throw new \BadMethodCallException('Not needed for this test.');
            }
        };
    }

    /**
     * @dataProvider forbiddenFilenameProvider
     */
    public function testForbiddenFilenamesAreRejected(string $filename): void
    {
        $this->assertFalse($this->createFactory()->isFilenameAllowed($filename));
    }

    public function forbiddenFilenameProvider(): array
    {
        return [
            ['evil.php'],
            ['shell.php.jpg'],
            ['.htaccess'],
            ['x.PHP'],
        ];
    }

    /**
     * @dataProvider allowedFilenameProvider
     */
    public function testAllowedFilenamesArePermitted(string $filename): void
    {
        $this->assertTrue($this->createFactory()->isFilenameAllowed($filename));
    }

    public function allowedFilenameProvider(): array
    {
        return [
            ['photo.jpg'],
            ['report.pdf'],
            ['noext'],
            ['logo.svg'],
        ];
    }

    public function testGetDocumentRejectsForbiddenFileType(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'roadiz_test_').'.php';
        file_put_contents($path, '<?php echo "evil"; ?>');

        try {
            $this->expectException(DocumentTypeNotAllowedException::class);
            $this->createFactory()->setFile(new File($path))->getDocument();
        } finally {
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }

    public function testGetDocumentSanitizesSvgContentBeforeStorage(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'roadiz_test_').'.svg';
        file_put_contents(
            $path,
            '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script><rect width="1" height="1"/></svg>'
        );

        $storage = $this->createMock(MountManager::class);
        $written = null;
        $storage->method('writeStream')->willReturnCallback(
            function (string $path, $stream) use (&$written): void {
                $written = stream_get_contents($stream);
            }
        );

        $document = $this->createMock(DocumentInterface::class);
        $document->method('getMountPath')->willReturn('documents/evil.svg');

        $factory = new class($storage, $this->createMock(DocumentFinderInterface::class), new NullLogger(), $document) extends AbstractDocumentFactory {
            public function __construct(
                MountManager $storage,
                DocumentFinderInterface $finder,
                NullLogger $logger,
                private readonly DocumentInterface $doc,
            ) {
                parent::__construct($storage, $finder, $logger);
            }

            protected function createDocument(): DocumentInterface
            {
                return $this->doc;
            }

            protected function persistDocument(DocumentInterface $document): void
            {
            }
        };

        try {
            $factory->setFile(new File($path))->getDocument();
        } finally {
            if (file_exists($path)) {
                unlink($path);
            }
        }

        $this->assertNotNull($written);
        $this->assertStringNotContainsString('<script', $written);
        $this->assertStringContainsString('<svg', $written);
    }
}
