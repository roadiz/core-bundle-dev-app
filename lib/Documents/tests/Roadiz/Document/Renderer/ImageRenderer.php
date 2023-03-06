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
use RZ\Roadiz\Documents\UrlGenerators\DocumentUrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class ImageRenderer extends atoum
{
    public function testSupports()
    {
        /** @var DocumentInterface $mockValidDocument */
        $mockValidDocument = new \mock\RZ\Roadiz\Documents\Models\SimpleDocument();
        $mockValidDocument->setFilename('file.jpg');
        $mockValidDocument->setMimeType('image/jpeg');

        /** @var DocumentInterface $mockExternalValidDocument */
        $mockExternalValidDocument = new \mock\RZ\Roadiz\Documents\Models\SimpleDocument();
        $mockExternalValidDocument->setFilename('file.jpg');
        $mockExternalValidDocument->setMimeType('image/jpeg');
        $mockExternalValidDocument->setEmbedId('xxxxx');
        $mockExternalValidDocument->setEmbedPlatform('getty');

        /** @var DocumentInterface $mockInvalidDocument */
        $mockInvalidDocument = new \mock\RZ\Roadiz\Documents\Models\SimpleDocument();
        $mockInvalidDocument->setFilename('file.psd');
        $mockInvalidDocument->setMimeType('image/vnd.adobe.photoshop');

        $this
            ->given($renderer = $this->newTestedInstance(
                $this->getFilesystemOperator(),
                $this->getEmbedFinderFactory(),
                $this->getEnvironment(),
                $this->getUrlGenerator()
            ))
            ->then
            ->string($mockValidDocument->getMimeType())
            ->isEqualTo('image/jpeg')
            ->boolean($renderer->supports($mockValidDocument, []))
            ->isEqualTo(true)
            ->boolean($renderer->supports($mockValidDocument, ['embed' => true]))
            ->isEqualTo(true)
            ->boolean($renderer->supports($mockExternalValidDocument, ['embed' => true]))
            ->isEqualTo(true)
            ->boolean($renderer->supports($mockValidDocument, ['picture' => true]))
            ->isEqualTo(false)
            ->string($mockInvalidDocument->getMimeType())
            ->isEqualTo('image/vnd.adobe.photoshop')
            ->boolean($renderer->supports($mockInvalidDocument, []))
            ->isEqualTo(false);
    }

    public function testRender()
    {
        /** @var DocumentInterface $mockDocument */
        $mockDocument = new \mock\RZ\Roadiz\Documents\Models\SimpleDocument();
        $mockDocument->setFilename('file.jpg');
        $mockDocument->setFolder('folder');
        $mockDocument->setMimeType('image/jpeg');

        $this
            ->given($renderer = $this->newTestedInstance(
                $this->getFilesystemOperator(),
                $this->getEmbedFinderFactory(),
                $this->getEnvironment(),
                $this->getUrlGenerator()
            ))
            ->then
            ->string($this->htmlTidy($renderer->render($mockDocument, ['noProcess' => true])))
            ->isEqualTo($this->htmlTidy(<<<EOT
<img alt="file.jpg" src="/files/folder/file.jpg" />
EOT
            ))
            ->string($this->htmlTidy($renderer->render($mockDocument, ['absolute' => true, 'noProcess' => true])))
            ->isEqualTo($this->htmlTidy(<<<EOT
<img alt="file.jpg" src="http://dummy.test/files/folder/file.jpg" />
EOT
            ))
            ->string($this->htmlTidy($renderer->render($mockDocument, [
                'width' => 300,
                'absolute' => true
            ])))
            ->isEqualTo($this->htmlTidy(<<<EOT
<img alt="file.jpg" src="http://dummy.test/assets/w300-q90/folder/file.jpg" width="300" />
EOT
            ))
            ->string($this->htmlTidy($renderer->render($mockDocument, [
                'width' => 300,
                'class' => 'awesome-image responsive',
                'absolute' => true
            ])))
            ->isEqualTo($this->htmlTidy(<<<EOT
<img alt="file.jpg"
    src="http://dummy.test/assets/w300-q90/folder/file.jpg"
    width="300"
    class="awesome-image responsive" />
EOT
            ))
            ->string($this->htmlTidy($renderer->render($mockDocument, [
                'width' => 300,
                'lazyload' => true
            ])))
            ->contains('noscript')
            ->isEqualTo($this->htmlTidy(<<<EOT
<img alt="file.jpg"
    data-src="/assets/w300-q90/folder/file.jpg"
    src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNcvGDBfwAGtQLk4581vAAAAABJRU5ErkJggg=="
    width="300"
    class="lazyload" />
<noscript>
    <img alt="file.jpg"
        src="/assets/w300-q90/folder/file.jpg"
        width="300" />
</noscript>
EOT
            ))
            ->string($this->htmlTidy($renderer->render($mockDocument, [
                'width' => 300,
                'lazyload' => true,
                'fallback' => 'https://test.test/fallback.png'
            ])))
            ->contains('noscript')
            ->isEqualTo($this->htmlTidy(<<<EOT
<img alt="file.jpg"
    data-src="/assets/w300-q90/folder/file.jpg"
    src="https://test.test/fallback.png"
    width="300"
    class="lazyload" />
<noscript>
    <img alt="file.jpg"
         src="/assets/w300-q90/folder/file.jpg"
         width="300" />
</noscript>
EOT
            ))
            ->string($this->htmlTidy($renderer->render($mockDocument, [
                'width' => 300,
                'fallback' => 'https://test.test/fallback.png'
            ])))
            ->isEqualTo($this->htmlTidy(<<<EOT
<img alt="file.jpg"
    src="/assets/w300-q90/folder/file.jpg"
    width="300" />
EOT
            ))
            ->string($this->htmlTidy($renderer->render($mockDocument, [
                'fit' => '600x400',
                'quality' => 70
            ])))
            ->isEqualTo($this->htmlTidy(<<<EOT
<img alt="file.jpg"
     src="/assets/f600x400-q70/folder/file.jpg"
     data-ratio="1.5"
     width="600"
     height="400" />
EOT
            ))
            ->string($this->htmlTidy($renderer->render($mockDocument, [
                'width' => 300,
                'lazyload' => true,
                'class' => 'awesome-image responsive',
            ])))
            ->contains('noscript')
            ->isEqualTo($this->htmlTidy(<<<EOT
<img alt="file.jpg"
    data-src="/assets/w300-q90/folder/file.jpg"
    src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNcvGDBfwAGtQLk4581vAAAAABJRU5ErkJggg=="
    width="300"
    class="awesome-image responsive lazyload" />
<noscript>
    <img alt="file.jpg"
    src="/assets/w300-q90/folder/file.jpg"
    width="300"
    class="awesome-image responsive" />
</noscript>
EOT
            ))
            ->string($this->htmlTidy($renderer->render($mockDocument, [
                'width' => 300,
                'srcset' => [[
                    'format' => [
                        'width' => 300
                    ],
                    'rule' => '1x'
                ],[
                    'format' => [
                        'width' => 600
                    ],
                    'rule' => '2x'
                ]]
            ])))
            ->isEqualTo($this->htmlTidy(<<<EOT
<img alt="file.jpg"
     src="/assets/w300-q90/folder/file.jpg"
     srcset="/assets/w300-q90/folder/file.jpg 1x, /assets/w600-q90/folder/file.jpg 2x"
     width="300" />
EOT
            ))
            ->string($this->htmlTidy($renderer->render($mockDocument, [
                'width' => 300,
                'srcset' => [[
                    'format' => [
                        'width' => 300
                    ],
                    'rule' => '1x'
                ],[
                    'format' => [
                        'width' => 600
                    ],
                    'rule' => '2x'
                ]],
                'sizes' => [
                    '(max-width: 767px) 300px',
                    '(min-width: 768px) 400px'
                ]
            ])))
            ->isEqualTo($this->htmlTidy(<<<EOT
<img alt="file.jpg"
     src="/assets/w300-q90/folder/file.jpg"
     srcset="/assets/w300-q90/folder/file.jpg 1x, /assets/w600-q90/folder/file.jpg 2x"
     sizes="(max-width: 767px) 300px, (min-width: 768px) 400px" />
EOT
            ))
            ->string($this->htmlTidy($renderer->render($mockDocument, [
                'fit' => '600x400',
                'srcset' => [[
                    'format' => [
                        'fit' => '600x400',
                    ],
                    'rule' => '1x'
                ],[
                    'format' => [
                        'fit' => '1200x800',
                    ],
                    'rule' => '2x'
                ]],
                'sizes' => [
                    '(max-width: 767px) 300px',
                    '(min-width: 768px) 400px'
                ]
            ])))
            ->isEqualTo($this->htmlTidy(<<<EOT
<img alt="file.jpg"
     src="/assets/f600x400-q90/folder/file.jpg"
     srcset="/assets/f600x400-q90/folder/file.jpg 1x, /assets/f1200x800-q90/folder/file.jpg 2x"
     sizes="(max-width: 767px) 300px, (min-width: 768px) 400px"
     data-ratio="1.5" />
EOT
            ))

            ->string($this->htmlTidy($renderer->render($mockDocument, [
                'fit' => '600x400',
                'loading' => 'lazy',
                'srcset' => [[
                    'format' => [
                        'fit' => '600x400',
                    ],
                    'rule' => '1x'
                ],[
                    'format' => [
                        'fit' => '1200x800',
                    ],
                    'rule' => '2x'
                ]],
                'sizes' => [
                    '(max-width: 767px) 300px',
                    '(min-width: 768px) 400px'
                ]
            ])))
            ->isEqualTo($this->htmlTidy(<<<EOT
<img alt="file.jpg"
     src="/assets/f600x400-q90/folder/file.jpg"
     srcset="/assets/f600x400-q90/folder/file.jpg 1x, /assets/f1200x800-q90/folder/file.jpg 2x"
     sizes="(max-width: 767px) 300px, (min-width: 768px) 400px"
     loading="lazy"
     data-ratio="1.5" />
EOT
            ))
            ->string($this->htmlTidy($renderer->render($mockDocument, [
                'fit' => '600x400',
                'lazyload' => true,
                'srcset' => [[
                    'format' => [
                        'fit' => '600x400',
                    ],
                    'rule' => '1x'
                ],[
                    'format' => [
                        'fit' => '1200x800',
                    ],
                    'rule' => '2x'
                ]],
                'sizes' => [
                    '(max-width: 767px) 300px',
                    '(min-width: 768px) 400px'
                ]
            ])))
            ->contains('noscript')
            ->isEqualTo($this->htmlTidy(<<<EOT
<img alt="file.jpg"
     data-src="/assets/f600x400-q90/folder/file.jpg"
     src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNcvGDBfwAGtQLk4581vAAAAABJRU5ErkJggg=="
     data-srcset="/assets/f600x400-q90/folder/file.jpg 1x, /assets/f1200x800-q90/folder/file.jpg 2x"
     sizes="(max-width: 767px) 300px, (min-width: 768px) 400px"
     data-ratio="1.5"
     class="lazyload" />
<noscript>
    <img alt="file.jpg"
         src="/assets/f600x400-q90/folder/file.jpg"
         srcset="/assets/f600x400-q90/folder/file.jpg 1x, /assets/f1200x800-q90/folder/file.jpg 2x"
         sizes="(max-width: 767px) 300px, (min-width: 768px) 400px"
         data-ratio="1.5" />
</noscript>
EOT
            ))
            ->string($this->htmlTidy($renderer->render($mockDocument, [
                'fit' => '600x400',
                'lazyload' => true,
                'loading' => 'lazy',
                'srcset' => [[
                    'format' => [
                        'fit' => '600x400',
                    ],
                    'rule' => '1x'
                ],[
                    'format' => [
                        'fit' => '1200x800',
                    ],
                    'rule' => '2x'
                ]],
                'sizes' => [
                    '(max-width: 767px) 300px',
                    '(min-width: 768px) 400px'
                ]
            ])))
            ->contains('noscript')
            ->isEqualTo($this->htmlTidy(<<<EOT
<img alt="file.jpg"
     data-src="/assets/f600x400-q90/folder/file.jpg"
     src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNcvGDBfwAGtQLk4581vAAAAABJRU5ErkJggg=="
     data-srcset="/assets/f600x400-q90/folder/file.jpg 1x, /assets/f1200x800-q90/folder/file.jpg 2x"
     sizes="(max-width: 767px) 300px, (min-width: 768px) 400px"
     loading="lazy"
     data-ratio="1.5"
     class="lazyload" />
<noscript>
    <img alt="file.jpg"
         src="/assets/f600x400-q90/folder/file.jpg"
         srcset="/assets/f600x400-q90/folder/file.jpg 1x, /assets/f1200x800-q90/folder/file.jpg 2x"
         sizes="(max-width: 767px) 300px, (min-width: 768px) 400px"
         loading="lazy"
         data-ratio="1.5" />
</noscript>
EOT
            ))
        ;
    }

    /**
     * @return \RZ\Roadiz\Documents\UrlGenerators\DocumentUrlGeneratorInterface
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

    private function htmlTidy(string $body): string
    {
        $body = preg_replace('#[\n\r\s]{2,}#', ' ', $body);
        $body = str_replace("&#x2F;", '/', $body);
        $body = html_entity_decode($body);
        return preg_replace('#\>[\n\r\s]+\<#', '><', $body);
    }

    private function getEnvironment(): Environment
    {
        $loader = new FilesystemLoader([
            dirname(__DIR__) . '/../../../src/Resources/views'
        ]);
        return new Environment($loader, [
            'autoescape' => false,
            'debug' => true
        ]);
    }

    /**
     * @return \RZ\Roadiz\Documents\MediaFinders\EmbedFinderFactory
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
