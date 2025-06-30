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
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class PictureRenderer extends atoum
{
    public function testIsEmbeddable()
    {
        /** @var \RZ\Roadiz\Documents\Models\DocumentInterface $mockExternalValidDocument */
        $mockExternalValidDocument = new \mock\RZ\Roadiz\Documents\Models\SimpleDocument();
        $mockExternalValidDocument->setFilename('file.jpg');
        $mockExternalValidDocument->setMimeType('image/jpeg');
        $mockExternalValidDocument->setEmbedId('xxxxx');
        $mockExternalValidDocument->setEmbedPlatform('getty');

        /** @var DocumentInterface $mockYoutubeDocument */
        $mockYoutubeDocument = new \mock\RZ\Roadiz\Documents\Models\SimpleDocument();
        $mockYoutubeDocument->setFilename('file.jpg');
        $mockYoutubeDocument->setMimeType('image/jpeg');
        $mockYoutubeDocument->setEmbedId('xxxxx');
        $mockYoutubeDocument->setEmbedPlatform('youtube');

        /** @var DocumentInterface $mockValidDocument */
        $mockValidDocument = new \mock\RZ\Roadiz\Documents\Models\SimpleDocument();
        $mockValidDocument->setFilename('file.jpg');
        $mockValidDocument->setMimeType('image/jpeg');

        $this
            ->given($renderer = $this->newTestedInstance(
                $this->getFilesystemOperator(),
                $this->getEmbedFinderFactory(),
                $this->getEnvironment(),
                $this->getUrlGenerator()
            ))
            ->then
            ->boolean($renderer->isEmbeddable($mockExternalValidDocument, ['embed' => true]))
            ->isEqualTo(false)
            ->boolean($renderer->isEmbeddable($mockValidDocument, ['embed' => true]))
            ->isEqualTo(false)
            ->boolean($renderer->isEmbeddable($mockValidDocument, []))
            ->isEqualTo(false)
            ->boolean($renderer->isEmbeddable($mockYoutubeDocument, []))
            ->isEqualTo(false)
            ->boolean($renderer->isEmbeddable($mockYoutubeDocument, ['embed' => true]))
            ->isEqualTo(true)
        ;
    }

    public function testSupports()
    {
        /** @var DocumentInterface $mockValidDocument */
        $mockValidDocument = new \mock\RZ\Roadiz\Documents\Models\SimpleDocument();
        $mockValidDocument->setFilename('file.jpg');
        $mockValidDocument->setMimeType('image/jpeg');

        /** @var DocumentInterface $mockInvalidDocument */
        $mockInvalidDocument = new \mock\RZ\Roadiz\Documents\Models\SimpleDocument();
        $mockInvalidDocument->setFilename('file.psd');
        $mockInvalidDocument->setMimeType('image/vnd.adobe.photoshop');

        /** @var DocumentInterface $mockExternalValidDocument */
        $mockExternalValidDocument = new \mock\RZ\Roadiz\Documents\Models\SimpleDocument();
        $mockExternalValidDocument->setFilename('file.jpg');
        $mockExternalValidDocument->setMimeType('image/jpeg');
        $mockExternalValidDocument->setEmbedId('xxxxx');
        $mockExternalValidDocument->setEmbedPlatform('getty');

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
            ->isEqualTo(false)
            ->boolean($renderer->supports($mockValidDocument, ['picture' => true]))
            ->isEqualTo(true)
            ->boolean($renderer->isEmbeddable($mockExternalValidDocument, ['picture' => true, 'embed' => true]))
            ->isEqualTo(false)
            ->boolean($mockExternalValidDocument->isImage())
            ->isEqualTo(true)
            ->boolean($renderer->supports($mockExternalValidDocument, ['picture' => true, 'embed' => true]))
            ->isEqualTo(true)
            ->boolean($renderer->supports($mockValidDocument, [
                'picture' => true,
                'embed' => true,
            ]))
            ->isEqualTo(true)
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

        /** @var DocumentInterface $mockWebpDocument */
        $mockWebpDocument = new \mock\RZ\Roadiz\Documents\Models\SimpleDocument();
        $mockWebpDocument->setFilename('file.webp');
        $mockWebpDocument->setFolder('folder');
        $mockWebpDocument->setMimeType('image/webp');

        $this
            ->given($renderer = $this->newTestedInstance(
                $this->getFilesystemOperator(),
                $this->getEmbedFinderFactory(),
                $this->getEnvironment(),
                $this->getUrlGenerator()
            ))
            ->then
            ->string($this->htmlTidy($renderer->render($mockDocument, [
                'noProcess' => true,
                'picture' => true
            ])))
            ->isEqualTo($this->htmlTidy(<<<EOT
<picture>
    <source type="image/webp" srcset="/files/folder/file.jpg.webp">
    <source type="image/jpeg" srcset="/files/folder/file.jpg">
    <img alt="" aria-hidden="true" src="/files/folder/file.jpg" />
</picture>
EOT
            ))
            ->string($this->htmlTidy($renderer->render($mockDocument, [
                'noProcess' => true,
                'picture' => true,
                'loading' => 'lazy',
            ])))
            ->isEqualTo($this->htmlTidy(<<<EOT
<picture>
    <source type="image/webp" srcset="/files/folder/file.jpg.webp">
    <source type="image/jpeg" srcset="/files/folder/file.jpg">
    <img alt="" aria-hidden="true" src="/files/folder/file.jpg" loading="lazy" />
</picture>
EOT
            ))
            ->string($this->htmlTidy($renderer->render($mockWebpDocument, [
                'noProcess' => true,
                'picture' => true
            ])))
            ->isEqualTo($this->htmlTidy(<<<EOT
<picture>
    <source type="image/webp" srcset="/files/folder/file.webp">
    <img alt="" aria-hidden="true" src="/files/folder/file.webp" />
</picture>
EOT
            ))
            ->string($this->htmlTidy($renderer->render($mockDocument, [
                'absolute' => true,
                'noProcess' => true,
                'picture' => true
            ])))
            ->isEqualTo($this->htmlTidy(<<<EOT
<picture>
    <source type="image/webp" srcset="http://dummy.test/files/folder/file.jpg.webp">
    <source type="image/jpeg" srcset="http://dummy.test/files/folder/file.jpg">
    <img alt="" aria-hidden="true" src="http://dummy.test/files/folder/file.jpg" />
</picture>
EOT
            ))
            ->string($this->htmlTidy($renderer->render($mockDocument, [
                'width' => 300,
                'absolute' => true,
                'picture' => true
            ])))
            ->isEqualTo($this->htmlTidy(<<<EOT
<picture>
<source type="image/webp" srcset="http://dummy.test/assets/w300-q90/folder/file.jpg.webp">
<source type="image/jpeg" srcset="http://dummy.test/assets/w300-q90/folder/file.jpg">
<img alt="" aria-hidden="true" src="http://dummy.test/assets/w300-q90/folder/file.jpg" width="300" />
</picture>
EOT
            ))
            ->string($this->htmlTidy($renderer->render($mockDocument, [
                'width' => 300,
                'class' => 'awesome-image responsive',
                'absolute' => true,
                'picture' => true
            ])))
            ->isEqualTo($this->htmlTidy(<<<EOT
<picture>
<source type="image/webp" srcset="http://dummy.test/assets/w300-q90/folder/file.jpg.webp">
<source type="image/jpeg" srcset="http://dummy.test/assets/w300-q90/folder/file.jpg">
<img alt="" aria-hidden="true" src="http://dummy.test/assets/w300-q90/folder/file.jpg" width="300" class="awesome-image responsive" />
</picture>
EOT
            ))
            ->string($this->htmlTidy($renderer->render($mockDocument, [
                'width' => 300,
                'lazyload' => true,
                'picture' => true
            ])))
            ->endWith('</noscript>')
            ->isEqualTo($this->htmlTidy(<<<EOT
<picture>
    <source type="image/webp"
            srcset="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNcvGDBfwAGtQLk4581vAAAAABJRU5ErkJggg=="
            data-srcset="/assets/w300-q90/folder/file.jpg.webp">
    <source type="image/jpeg"
            srcset="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNcvGDBfwAGtQLk4581vAAAAABJRU5ErkJggg=="
            data-srcset="/assets/w300-q90/folder/file.jpg">
    <img alt="" aria-hidden="true"
         data-src="/assets/w300-q90/folder/file.jpg"
         src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNcvGDBfwAGtQLk4581vAAAAABJRU5ErkJggg=="
         width="300"
         class="lazyload" />
</picture>
<noscript>
    <picture>
        <source type="image/webp"
                srcset="/assets/w300-q90/folder/file.jpg.webp">
        <source type="image/jpeg"
                srcset="/assets/w300-q90/folder/file.jpg">
        <img alt="" aria-hidden="true"
             src="/assets/w300-q90/folder/file.jpg"
             width="300" />
    </picture>
</noscript>
EOT
            ))
            ->string($this->htmlTidy($renderer->render($mockDocument, [
                'width' => 300,
                'lazyload' => true,
                'picture' => true,
                'fallback' => 'https://test.test/fallback.png'
            ])))
            ->endWith('</noscript>')
            ->isEqualTo($this->htmlTidy(<<<EOT
<picture>
    <source type="image/webp" srcset="https://test.test/fallback.png" data-srcset="/assets/w300-q90/folder/file.jpg.webp">
    <source type="image/jpeg" srcset="https://test.test/fallback.png" data-srcset="/assets/w300-q90/folder/file.jpg">
    <img alt="" aria-hidden="true"
         data-src="/assets/w300-q90/folder/file.jpg"
         src="https://test.test/fallback.png"
         width="300"
         class="lazyload" />
</picture>
<noscript>
    <picture>
        <source type="image/webp" srcset="/assets/w300-q90/folder/file.jpg.webp">
        <source type="image/jpeg" srcset="/assets/w300-q90/folder/file.jpg">
        <img alt="" aria-hidden="true"
            src="/assets/w300-q90/folder/file.jpg"
            width="300" />
    </picture>
</noscript>
EOT
            ))
            ->string($this->htmlTidy($renderer->render($mockDocument, [
                'width' => 300,
                'fallback' => 'https://test.test/fallback.png',
                'picture' => true
            ])))
            ->isEqualTo($this->htmlTidy(<<<EOT
<picture>
    <source type="image/webp" srcset="/assets/w300-q90/folder/file.jpg.webp">
    <source type="image/jpeg" srcset="/assets/w300-q90/folder/file.jpg">
    <img alt="" aria-hidden="true"
        src="/assets/w300-q90/folder/file.jpg"
        width="300" />
</picture>
EOT
            ))
            ->string($this->htmlTidy($renderer->render($mockDocument, [
                'width' => 300,
                'lazyload' => true,
                'class' => 'awesome-image responsive',
                'picture' => true
            ])))
            ->endWith('</noscript>')
            ->isEqualTo($this->htmlTidy(<<<EOT
<picture>
    <source type="image/webp"
            srcset="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNcvGDBfwAGtQLk4581vAAAAABJRU5ErkJggg=="
            data-srcset="/assets/w300-q90/folder/file.jpg.webp">
    <source type="image/jpeg"
            srcset="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNcvGDBfwAGtQLk4581vAAAAABJRU5ErkJggg=="
            data-srcset="/assets/w300-q90/folder/file.jpg">
    <img alt="" aria-hidden="true"
         data-src="/assets/w300-q90/folder/file.jpg"
         src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNcvGDBfwAGtQLk4581vAAAAABJRU5ErkJggg=="
         width="300"
         class="awesome-image responsive lazyload" />
</picture>
<noscript>
    <picture>
        <source type="image/webp"
                srcset="/assets/w300-q90/folder/file.jpg.webp">
        <source type="image/jpeg"
                srcset="/assets/w300-q90/folder/file.jpg">
        <img alt="" aria-hidden="true"
             src="/assets/w300-q90/folder/file.jpg"
             width="300"
             class="awesome-image responsive" />
    </picture>
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
                ]],
                'picture' => true
            ])))
            ->isEqualTo($this->htmlTidy(<<<EOT
<picture>
    <source type="image/webp"
            srcset="/assets/w300-q90/folder/file.jpg.webp 1x, /assets/w600-q90/folder/file.jpg.webp 2x">
    <source type="image/jpeg"
            srcset="/assets/w300-q90/folder/file.jpg 1x, /assets/w600-q90/folder/file.jpg 2x">
    <img alt="" aria-hidden="true"
        src="/assets/w300-q90/folder/file.jpg"
        srcset="/assets/w300-q90/folder/file.jpg 1x, /assets/w600-q90/folder/file.jpg 2x"
        width="300" />
</picture>
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
                ],
                'picture' => true
            ])))
            ->isEqualTo($this->htmlTidy(<<<EOT
<picture>
    <source type="image/webp"
            sizes="(max-width: 767px) 300px, (min-width: 768px) 400px"
            srcset="/assets/w300-q90/folder/file.jpg.webp 1x, /assets/w600-q90/folder/file.jpg.webp 2x">
    <source type="image/jpeg"
            sizes="(max-width: 767px) 300px, (min-width: 768px) 400px"
            srcset="/assets/w300-q90/folder/file.jpg 1x, /assets/w600-q90/folder/file.jpg 2x">
    <img alt="" aria-hidden="true"
        src="/assets/w300-q90/folder/file.jpg"
        srcset="/assets/w300-q90/folder/file.jpg 1x, /assets/w600-q90/folder/file.jpg 2x"
        sizes="(max-width: 767px) 300px, (min-width: 768px) 400px" />
</picture>
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
                ],
                'picture' => true
            ])))
            ->isEqualTo($this->htmlTidy(<<<EOT
<picture>
    <source type="image/webp"
            sizes="(max-width: 767px) 300px, (min-width: 768px) 400px"
            srcset="/assets/f600x400-q90/folder/file.jpg.webp 1x, /assets/f1200x800-q90/folder/file.jpg.webp 2x">
    <source type="image/jpeg"
            sizes="(max-width: 767px) 300px, (min-width: 768px) 400px"
            srcset="/assets/f600x400-q90/folder/file.jpg 1x, /assets/f1200x800-q90/folder/file.jpg 2x">
    <img alt="" aria-hidden="true"
        src="/assets/f600x400-q90/folder/file.jpg"
        srcset="/assets/f600x400-q90/folder/file.jpg 1x, /assets/f1200x800-q90/folder/file.jpg 2x"
        sizes="(max-width: 767px) 300px, (min-width: 768px) 400px"
        data-ratio="1.5" />
</picture>
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
                ],
                'picture' => true
            ])))
            ->isEqualTo($this->htmlTidy(<<<EOT
<picture>
    <source type="image/webp"
            sizes="(max-width: 767px) 300px, (min-width: 768px) 400px"
            srcset="/assets/f600x400-q90/folder/file.jpg.webp 1x, /assets/f1200x800-q90/folder/file.jpg.webp 2x">
    <source type="image/jpeg"
            sizes="(max-width: 767px) 300px, (min-width: 768px) 400px"
            srcset="/assets/f600x400-q90/folder/file.jpg 1x, /assets/f1200x800-q90/folder/file.jpg 2x">
    <img alt="" aria-hidden="true"
        src="/assets/f600x400-q90/folder/file.jpg"
        srcset="/assets/f600x400-q90/folder/file.jpg 1x, /assets/f1200x800-q90/folder/file.jpg 2x"
        sizes="(max-width: 767px) 300px, (min-width: 768px) 400px"
        loading="lazy"
        data-ratio="1.5" />
</picture>
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
                'picture' => true
            ])))
            ->endWith('</noscript>')
            ->isEqualTo($this->htmlTidy(<<<EOT
<picture>
    <source type="image/webp"
            srcset="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNcvGDBfwAGtQLk4581vAAAAABJRU5ErkJggg=="
            data-srcset="/assets/f600x400-q90/folder/file.jpg.webp 1x, /assets/f1200x800-q90/folder/file.jpg.webp 2x">
    <source type="image/jpeg"
            srcset="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNcvGDBfwAGtQLk4581vAAAAABJRU5ErkJggg=="
            data-srcset="/assets/f600x400-q90/folder/file.jpg 1x, /assets/f1200x800-q90/folder/file.jpg 2x">
    <img alt="" aria-hidden="true"
         data-src="/assets/f600x400-q90/folder/file.jpg"
         src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNcvGDBfwAGtQLk4581vAAAAABJRU5ErkJggg=="
         data-srcset="/assets/f600x400-q90/folder/file.jpg 1x, /assets/f1200x800-q90/folder/file.jpg 2x"
         data-ratio="1.5"
         width="600"
         height="400"
         class="lazyload" />
</picture>
<noscript>
    <picture>
        <source type="image/webp"
                srcset="/assets/f600x400-q90/folder/file.jpg.webp 1x, /assets/f1200x800-q90/folder/file.jpg.webp 2x">
        <source type="image/jpeg"
                srcset="/assets/f600x400-q90/folder/file.jpg 1x, /assets/f1200x800-q90/folder/file.jpg 2x">
        <img alt="" aria-hidden="true"
             src="/assets/f600x400-q90/folder/file.jpg"
             srcset="/assets/f600x400-q90/folder/file.jpg 1x, /assets/f1200x800-q90/folder/file.jpg 2x"
             data-ratio="1.5"
             width="600"
             height="400" />
    </picture>
</noscript>
EOT
            ))
            ->string($this->htmlTidy($renderer->render($mockDocument, [
                'fit' => '600x400',
                'lazyload' => true,
                'fallback' => 'https://test.test/fallback.png',
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
                'picture' => true
            ])))
            ->endWith('</noscript>')
            ->isEqualTo($this->htmlTidy(<<<EOT
<picture>
    <source type="image/webp"
            srcset="https://test.test/fallback.png"
            data-srcset="/assets/f600x400-q90/folder/file.jpg.webp 1x, /assets/f1200x800-q90/folder/file.jpg.webp 2x">
    <source type="image/jpeg"
            srcset="https://test.test/fallback.png"
            data-srcset="/assets/f600x400-q90/folder/file.jpg 1x, /assets/f1200x800-q90/folder/file.jpg 2x">
    <img alt="" aria-hidden="true"
         data-src="/assets/f600x400-q90/folder/file.jpg"
         src="https://test.test/fallback.png"
         data-srcset="/assets/f600x400-q90/folder/file.jpg 1x, /assets/f1200x800-q90/folder/file.jpg 2x"
         data-ratio="1.5"
         width="600"
         height="400"
         class="lazyload" />
</picture>
<noscript>
    <picture>
        <source type="image/webp"
                srcset="/assets/f600x400-q90/folder/file.jpg.webp 1x, /assets/f1200x800-q90/folder/file.jpg.webp 2x">
        <source type="image/jpeg"
                srcset="/assets/f600x400-q90/folder/file.jpg 1x, /assets/f1200x800-q90/folder/file.jpg 2x">
        <img alt="" aria-hidden="true"
            src="/assets/f600x400-q90/folder/file.jpg"
            srcset="/assets/f600x400-q90/folder/file.jpg 1x, /assets/f1200x800-q90/folder/file.jpg 2x"
            data-ratio="1.5"
            width="600"
            height="400" />
    </picture>
</noscript>
EOT
            ))
            ->string($this->htmlTidy($renderer->render($mockDocument, [
                'fit' => '600x400',
                'media' => [[
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
                    'rule' => '(min-width: 600px)'
                ]],
                'picture' => true
            ])))
            ->isEqualTo($this->htmlTidy(<<<EOT
<picture>
    <source type="image/webp"
            media="(min-width: 600px)"
            srcset="/assets/f600x400-q90/folder/file.jpg.webp 1x, /assets/f1200x800-q90/folder/file.jpg.webp 2x">
    <source type="image/jpeg"
            media="(min-width: 600px)"
            srcset="/assets/f600x400-q90/folder/file.jpg 1x, /assets/f1200x800-q90/folder/file.jpg 2x">
    <img alt="" aria-hidden="true"
         src="/assets/f600x400-q90/folder/file.jpg"
         data-ratio="1.5"
         width="600"
         height="400" />
</picture>
EOT
            ))
            ->string($this->htmlTidy($renderer->render($mockDocument, [
                'fit' => '600x400',
                'media' => [[
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
                    'rule' => '(min-width: 600px)'
                ],[
                    'srcset' => [[
                        'format' => [
                            'fit' => '1200x800',
                        ],
                        'rule' => '1x'
                    ],[
                        'format' => [
                            'fit' => '2400x1600',
                        ],
                        'rule' => '2x'
                    ]],
                    'rule' => '(min-width: 1200px)'
                ]],
                'picture' => true
            ])))
            ->isEqualTo($this->htmlTidy(<<<EOT
<picture>
    <source type="image/webp"
            media="(min-width: 600px)"
            srcset="/assets/f600x400-q90/folder/file.jpg.webp 1x, /assets/f1200x800-q90/folder/file.jpg.webp 2x">
    <source type="image/jpeg"
            media="(min-width: 600px)"
            srcset="/assets/f600x400-q90/folder/file.jpg 1x, /assets/f1200x800-q90/folder/file.jpg 2x">

    <source type="image/webp"
            media="(min-width: 1200px)"
            srcset="/assets/f1200x800-q90/folder/file.jpg.webp 1x, /assets/f2400x1600-q90/folder/file.jpg.webp 2x">
    <source type="image/jpeg"
            media="(min-width: 1200px)"
            srcset="/assets/f1200x800-q90/folder/file.jpg 1x, /assets/f2400x1600-q90/folder/file.jpg 2x">

    <img alt="" aria-hidden="true"
         src="/assets/f600x400-q90/folder/file.jpg"
         data-ratio="1.5"
         width="600"
         height="400" />
</picture>
EOT
            ))
            ->string($this->htmlTidy($renderer->render($mockDocument, [
                'fit' => '600x400',
                'loading' => 'lazy',
                'media' => [[
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
                    'rule' => '(min-width: 600px)'
                ],[
                    'srcset' => [[
                        'format' => [
                            'fit' => '1200x800',
                        ],
                        'rule' => '1x'
                    ],[
                        'format' => [
                            'fit' => '2400x1600',
                        ],
                        'rule' => '2x'
                    ]],
                    'rule' => '(min-width: 1200px)'
                ]],
                'picture' => true
            ])))
            ->isEqualTo($this->htmlTidy(<<<EOT
<picture>
    <source type="image/webp"
            media="(min-width: 600px)"
            srcset="/assets/f600x400-q90/folder/file.jpg.webp 1x, /assets/f1200x800-q90/folder/file.jpg.webp 2x">
    <source type="image/jpeg"
            media="(min-width: 600px)"
            srcset="/assets/f600x400-q90/folder/file.jpg 1x, /assets/f1200x800-q90/folder/file.jpg 2x">

    <source type="image/webp"
            media="(min-width: 1200px)"
            srcset="/assets/f1200x800-q90/folder/file.jpg.webp 1x, /assets/f2400x1600-q90/folder/file.jpg.webp 2x">
    <source type="image/jpeg"
            media="(min-width: 1200px)"
            srcset="/assets/f1200x800-q90/folder/file.jpg 1x, /assets/f2400x1600-q90/folder/file.jpg 2x">

    <img alt="" aria-hidden="true"
         src="/assets/f600x400-q90/folder/file.jpg"
         loading="lazy"
         data-ratio="1.5"
         width="600"
         height="400" />
</picture>
EOT
            ))
            ->string($this->htmlTidy($renderer->render($mockWebpDocument, [
                'fit' => '600x400',
                'lazyload' => true,
                'fallback' => 'FALLBACK',
                'media' => [[
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
                    'rule' => '(min-width: 600px)'
                ],[
                    'srcset' => [[
                        'format' => [
                            'fit' => '1200x800',
                        ],
                        'rule' => '1x'
                    ],[
                        'format' => [
                            'fit' => '2400x1600',
                        ],
                        'rule' => '2x'
                    ]],
                    'rule' => '(min-width: 1200px)'
                ]],
                'picture' => true
            ])))
            ->isEqualTo($this->htmlTidy(<<<EOT
<picture>
    <source type="image/webp"
            media="(min-width: 600px)"
            srcset="FALLBACK"
            data-srcset="/assets/f600x400-q90/folder/file.webp 1x, /assets/f1200x800-q90/folder/file.webp 2x">

    <source type="image/webp"
            media="(min-width: 1200px)"
            srcset="FALLBACK"
            data-srcset="/assets/f1200x800-q90/folder/file.webp 1x, /assets/f2400x1600-q90/folder/file.webp 2x">

    <img alt="" aria-hidden="true"
         data-src="/assets/f600x400-q90/folder/file.webp"
         src="FALLBACK"
         data-ratio="1.5"
         width="600"
         height="400"
         class="lazyload" />
</picture>
<noscript>
    <picture>
        <source type="image/webp"
                media="(min-width: 600px)"
                srcset="/assets/f600x400-q90/folder/file.webp 1x, /assets/f1200x800-q90/folder/file.webp 2x">

        <source type="image/webp"
                media="(min-width: 1200px)"
                srcset="/assets/f1200x800-q90/folder/file.webp 1x, /assets/f2400x1600-q90/folder/file.webp 2x">

        <img alt="" aria-hidden="true"
             src="/assets/f600x400-q90/folder/file.webp"
             data-ratio="1.5"
             width="600"
             height="400" />
    </picture>
</noscript>
EOT
            ))
            ->string($this->htmlTidy($renderer->render($mockWebpDocument, [
                'fit' => '600x400',
                'lazyload' => true,
                'loading' => 'lazy',
                'fallback' => 'FALLBACK',
                'media' => [[
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
                    'rule' => '(min-width: 600px)'
                ],[
                    'srcset' => [[
                        'format' => [
                            'fit' => '1200x800',
                        ],
                        'rule' => '1x'
                    ],[
                        'format' => [
                            'fit' => '2400x1600',
                        ],
                        'rule' => '2x'
                    ]],
                    'rule' => '(min-width: 1200px)'
                ]],
                'picture' => true
            ])))
            ->isEqualTo($this->htmlTidy(<<<EOT
<picture>
    <source type="image/webp"
            media="(min-width: 600px)"
            srcset="FALLBACK"
            data-srcset="/assets/f600x400-q90/folder/file.webp 1x, /assets/f1200x800-q90/folder/file.webp 2x">

    <source type="image/webp"
            media="(min-width: 1200px)"
            srcset="FALLBACK"
            data-srcset="/assets/f1200x800-q90/folder/file.webp 1x, /assets/f2400x1600-q90/folder/file.webp 2x">

    <img alt="" aria-hidden="true"
         data-src="/assets/f600x400-q90/folder/file.webp"
         src="FALLBACK"
         loading="lazy"
         data-ratio="1.5"
         width="600"
         height="400"
         class="lazyload" />
</picture>
<noscript>
    <picture>
        <source type="image/webp"
                media="(min-width: 600px)"
                srcset="/assets/f600x400-q90/folder/file.webp 1x, /assets/f1200x800-q90/folder/file.webp 2x">

        <source type="image/webp"
                media="(min-width: 1200px)"
                srcset="/assets/f1200x800-q90/folder/file.webp 1x, /assets/f2400x1600-q90/folder/file.webp 2x">

        <img alt="" aria-hidden="true"
             src="/assets/f600x400-q90/folder/file.webp"
             loading="lazy"
             data-ratio="1.5"
             width="600"
             height="400" />
    </picture>
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
