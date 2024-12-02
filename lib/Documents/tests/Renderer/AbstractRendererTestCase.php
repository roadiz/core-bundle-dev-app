<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Tests\Renderer;

use League\Flysystem\Config;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\MountManager;
use League\Flysystem\UrlGeneration\PublicUrlGenerator;
use PHPUnit\Framework\TestCase;
use RZ\Roadiz\Documents\MediaFinders\EmbedFinderFactory;
use RZ\Roadiz\Documents\Tests\MediaFinders\SimpleVimeoEmbedFinder;
use RZ\Roadiz\Documents\Tests\MediaFinders\SimpleYoutubeEmbedFinder;
use RZ\Roadiz\Documents\UrlGenerators\DocumentUrlGeneratorInterface;
use RZ\Roadiz\Documents\UrlGenerators\DummyDocumentUrlGenerator;
use Symfony\Component\HttpClient\HttpClient;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

abstract class AbstractRendererTestCase extends TestCase
{
    protected function htmlTidy(string $body): string
    {
        $body = preg_replace('#[\n\r\t\s]{2,}#', ' ', $body);
        $body = str_replace('&#x2F;', '/', $body);
        $body = html_entity_decode($body);

        return preg_replace('#\>[\n\r\t\s]+\<#', '><', $body);
    }

    protected function getUrlGenerator(): DocumentUrlGeneratorInterface
    {
        return new DummyDocumentUrlGenerator();
    }

    protected function getFilesystemOperator(): FilesystemOperator
    {
        return new MountManager([
            'public' => new Filesystem(
                new LocalFilesystemAdapter(dirname(__DIR__).'/../files/'),
                publicUrlGenerator: new class implements PublicUrlGenerator {
                    public function publicUrl(string $path, Config $config): string
                    {
                        return '/files/'.$path;
                    }
                }
            ),
            'private' => new Filesystem(
                new LocalFilesystemAdapter(dirname(__DIR__).'/../files/'),
                publicUrlGenerator: new class implements PublicUrlGenerator {
                    public function publicUrl(string $path, Config $config): string
                    {
                        return '/files/'.$path;
                    }
                }
            ),
        ]);
    }

    protected function getEnvironment(): Environment
    {
        $loader = new FilesystemLoader([
            dirname(__DIR__).'/../src/Resources/views',
        ]);

        return new Environment($loader, [
            'autoescape' => false,
        ]);
    }

    protected function getEmbedFinderFactory(): EmbedFinderFactory
    {
        return new EmbedFinderFactory(HttpClient::create(), [
            'youtube' => SimpleYoutubeEmbedFinder::class,
            'vimeo' => SimpleVimeoEmbedFinder::class,
        ]);
    }

    public function assertHtmlTidyEquals(string $expected, string $actual, string $message = ''): void
    {
        $this->assertEquals(
            $this->htmlTidy($expected),
            $this->htmlTidy($actual),
            $message
        );
    }
}
