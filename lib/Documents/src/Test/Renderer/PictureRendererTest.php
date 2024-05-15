<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Test\Renderer;

use RZ\Roadiz\Documents\Models\SimpleDocument;
use RZ\Roadiz\Documents\Renderer\PictureRenderer;

class PictureRendererTest extends AbstractRendererTestCase
{
    protected function getRenderer(): PictureRenderer
    {
        return new PictureRenderer(
            $this->getFilesystemOperator(),
            $this->getEmbedFinderFactory(),
            $this->getEnvironment(),
            $this->getUrlGenerator()
        );
    }

    public function testIsEmbeddable(): void
    {
        $mockExternalValidDocument = new SimpleDocument();
        $mockExternalValidDocument->setFilename('file.jpg');
        $mockExternalValidDocument->setMimeType('image/jpeg');
        $mockExternalValidDocument->setEmbedId('xxxxx');
        $mockExternalValidDocument->setEmbedPlatform('getty');

        $mockYoutubeDocument = new SimpleDocument();
        $mockYoutubeDocument->setFilename('file.jpg');
        $mockYoutubeDocument->setMimeType('image/jpeg');
        $mockYoutubeDocument->setEmbedId('xxxxx');
        $mockYoutubeDocument->setEmbedPlatform('youtube');

        $mockValidDocument = new SimpleDocument();
        $mockValidDocument->setFilename('file.jpg');
        $mockValidDocument->setMimeType('image/jpeg');

        $renderer = $this->getRenderer();

        $this->assertFalse(
            $renderer->isEmbeddable($mockExternalValidDocument, ['embed' => true])
        );
        $this->assertFalse(
            $renderer->isEmbeddable($mockValidDocument, ['embed' => true])
        );
        $this->assertFalse(
            $renderer->isEmbeddable($mockValidDocument, [])
        );
        $this->assertFalse(
            $renderer->isEmbeddable($mockYoutubeDocument, [])
        );
        $this->assertTrue(
            $renderer->isEmbeddable($mockYoutubeDocument, ['embed' => true])
        );
    }

    public function testSupports(): void
    {
        $mockValidDocument = new SimpleDocument();
        $mockValidDocument->setFilename('file.jpg');
        $mockValidDocument->setMimeType('image/jpeg');

        $mockInvalidDocument = new SimpleDocument();
        $mockInvalidDocument->setFilename('file.psd');
        $mockInvalidDocument->setMimeType('image/vnd.adobe.photoshop');

        $mockExternalValidDocument = new SimpleDocument();
        $mockExternalValidDocument->setFilename('file.jpg');
        $mockExternalValidDocument->setMimeType('image/jpeg');
        $mockExternalValidDocument->setEmbedId('xxxxx');
        $mockExternalValidDocument->setEmbedPlatform('getty');

        $renderer = $this->getRenderer();

        $this->assertEquals(
            'image/jpeg',
            $mockValidDocument->getMimeType()
        );
        $this->assertFalse(
            $renderer->supports($mockValidDocument, [])
        );
        $this->assertTrue(
            $renderer->supports($mockValidDocument, ['picture' => true])
        );
        $this->assertFalse(
            $renderer->isEmbeddable($mockExternalValidDocument, ['picture' => true, 'embed' => true])
        );
        $this->assertTrue(
            $mockExternalValidDocument->isImage()
        );
        $this->assertTrue(
            $renderer->supports($mockExternalValidDocument, ['picture' => true, 'embed' => true])
        );
        $this->assertTrue(
            $renderer->supports($mockValidDocument, [
                'picture' => true,
                'embed' => true,
            ])
        );
        $this->assertEquals(
            'image/vnd.adobe.photoshop',
            $mockInvalidDocument->getMimeType()
        );
        $this->assertFalse(
            $renderer->supports($mockInvalidDocument, [])
        );
    }

    public function testRender(): void
    {
        $mockDocument = new SimpleDocument();
        $mockDocument->setFilename('file.jpg');
        $mockDocument->setFolder('folder');
        $mockDocument->setMimeType('image/jpeg');

        $mockWebpDocument = new SimpleDocument();
        $mockWebpDocument->setFilename('file.webp');
        $mockWebpDocument->setFolder('folder');
        $mockWebpDocument->setMimeType('image/webp');

        $renderer = $this->getRenderer();

        $this->assertHtmlTidyEquals(
            <<<EOT
<picture>
    <source type="image/webp" srcset="/files/folder/file.jpg.webp">
    <source type="image/jpeg" srcset="/files/folder/file.jpg">
    <img alt="file.jpg" src="/files/folder/file.jpg" />
</picture>
EOT
            ,
            $renderer->render($mockDocument, [
                'noProcess' => true,
                'picture' => true
            ])
        );

        $this->assertHtmlTidyEquals(
            <<<EOT
<picture>
    <source type="image/webp" srcset="/files/folder/file.jpg.webp">
    <source type="image/jpeg" srcset="/files/folder/file.jpg">
    <img alt="file.jpg" src="/files/folder/file.jpg" loading="lazy" />
</picture>
EOT
            ,
            $renderer->render($mockDocument, [
                'noProcess' => true,
                'picture' => true,
                'loading' => 'lazy',
            ])
        );

        $this->assertHtmlTidyEquals(
            <<<EOT
<picture>
    <source type="image/webp" srcset="/files/folder/file.webp">
    <img alt="file.webp" src="/files/folder/file.webp" />
</picture>
EOT
            ,
            $renderer->render($mockWebpDocument, [
                'noProcess' => true,
                'picture' => true
            ])
        );

        $this->assertHtmlTidyEquals(
            <<<EOT
<picture>
    <source type="image/webp" srcset="http://dummy.test/files/folder/file.jpg.webp">
    <source type="image/jpeg" srcset="http://dummy.test/files/folder/file.jpg">
    <img alt="file.jpg" src="http://dummy.test/files/folder/file.jpg" />
</picture>
EOT
            ,
            $renderer->render($mockDocument, [
                'absolute' => true,
                'noProcess' => true,
                'picture' => true
            ])
        );

        $this->assertHtmlTidyEquals(
            $renderer->render($mockDocument, [
                'width' => 300,
                'absolute' => true,
                'picture' => true
            ]),
            <<<EOT
<picture>
<source type="image/webp" srcset="http://dummy.test/assets/w300-q90/folder/file.jpg.webp">
<source type="image/jpeg" srcset="http://dummy.test/assets/w300-q90/folder/file.jpg">
<img alt="file.jpg" src="http://dummy.test/assets/w300-q90/folder/file.jpg" width="300" />
</picture>
EOT
        );

        $this->assertHtmlTidyEquals(
            $renderer->render($mockDocument, [
                'width' => 300,
                'class' => 'awesome-image responsive',
                'absolute' => true,
                'picture' => true
            ]),
            <<<EOT
<picture>
<source type="image/webp" srcset="http://dummy.test/assets/w300-q90/folder/file.jpg.webp">
<source type="image/jpeg" srcset="http://dummy.test/assets/w300-q90/folder/file.jpg">
<img alt="file.jpg" src="http://dummy.test/assets/w300-q90/folder/file.jpg" width="300" class="awesome-image responsive" />
</picture>
EOT
        );

        $this->assertHtmlTidyEquals(
            $renderer->render($mockDocument, [
                'width' => 300,
                'lazyload' => true,
                'picture' => true
            ]),
            <<<EOT
<picture>
    <source type="image/webp"
            srcset="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNcvGDBfwAGtQLk4581vAAAAABJRU5ErkJggg=="
            data-srcset="/assets/w300-q90/folder/file.jpg.webp">
    <source type="image/jpeg"
            srcset="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNcvGDBfwAGtQLk4581vAAAAABJRU5ErkJggg=="
            data-srcset="/assets/w300-q90/folder/file.jpg">
    <img alt="file.jpg"
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
        <img alt="file.jpg"
             src="/assets/w300-q90/folder/file.jpg"
             width="300" />
    </picture>
</noscript>
EOT
        );

        $this->assertHtmlTidyEquals(
            $renderer->render($mockDocument, [
                'width' => 300,
                'lazyload' => true,
                'picture' => true,
                'fallback' => 'https://test.test/fallback.png'
            ]),
            <<<EOT
<picture>
    <source type="image/webp" srcset="https://test.test/fallback.png" data-srcset="/assets/w300-q90/folder/file.jpg.webp">
    <source type="image/jpeg" srcset="https://test.test/fallback.png" data-srcset="/assets/w300-q90/folder/file.jpg">
    <img alt="file.jpg"
         data-src="/assets/w300-q90/folder/file.jpg"
         src="https://test.test/fallback.png"
         width="300"
         class="lazyload" />
</picture>
<noscript>
    <picture>
        <source type="image/webp" srcset="/assets/w300-q90/folder/file.jpg.webp">
        <source type="image/jpeg" srcset="/assets/w300-q90/folder/file.jpg">
        <img alt="file.jpg"
            src="/assets/w300-q90/folder/file.jpg"
            width="300" />
    </picture>
</noscript>
EOT
        );
        $this->assertHtmlTidyEquals(
            $renderer->render($mockDocument, [
                'width' => 300,
                'fallback' => 'https://test.test/fallback.png',
                'picture' => true
            ]),
            <<<EOT
<picture>
    <source type="image/webp" srcset="/assets/w300-q90/folder/file.jpg.webp">
    <source type="image/jpeg" srcset="/assets/w300-q90/folder/file.jpg">
    <img alt="file.jpg"
        src="/assets/w300-q90/folder/file.jpg"
        width="300" />
</picture>
EOT
        );
        $this->assertHtmlTidyEquals(
            $renderer->render($mockDocument, [
                'width' => 300,
                'lazyload' => true,
                'class' => 'awesome-image responsive',
                'picture' => true
            ]),
            <<<EOT
<picture>
    <source type="image/webp"
            srcset="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNcvGDBfwAGtQLk4581vAAAAABJRU5ErkJggg=="
            data-srcset="/assets/w300-q90/folder/file.jpg.webp">
    <source type="image/jpeg"
            srcset="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNcvGDBfwAGtQLk4581vAAAAABJRU5ErkJggg=="
            data-srcset="/assets/w300-q90/folder/file.jpg">
    <img alt="file.jpg"
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
        <img alt="file.jpg"
             src="/assets/w300-q90/folder/file.jpg"
             width="300"
             class="awesome-image responsive" />
    </picture>
</noscript>
EOT
        );
        $this->assertHtmlTidyEquals(
            $renderer->render($mockDocument, [
                'width' => 300,
                'srcset' => [[
                    'format' => [
                        'width' => 300
                    ],
                    'rule' => '1x'
                ], [
                    'format' => [
                        'width' => 600
                    ],
                    'rule' => '2x'
                ]],
                'picture' => true
            ]),
            <<<EOT
<picture>
    <source type="image/webp"
            srcset="/assets/w300-q90/folder/file.jpg.webp 1x, /assets/w600-q90/folder/file.jpg.webp 2x">
    <source type="image/jpeg"
            srcset="/assets/w300-q90/folder/file.jpg 1x, /assets/w600-q90/folder/file.jpg 2x">
    <img alt="file.jpg"
        src="/assets/w300-q90/folder/file.jpg"
        srcset="/assets/w300-q90/folder/file.jpg 1x, /assets/w600-q90/folder/file.jpg 2x"
        width="300" />
</picture>
EOT
        );
        $this->assertHtmlTidyEquals(
            $renderer->render($mockDocument, [
                'width' => 300,
                'srcset' => [[
                    'format' => [
                        'width' => 300
                    ],
                    'rule' => '1x'
                ], [
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
            ]),
            <<<EOT
<picture>
    <source type="image/webp"
            sizes="(max-width: 767px) 300px, (min-width: 768px) 400px"
            srcset="/assets/w300-q90/folder/file.jpg.webp 1x, /assets/w600-q90/folder/file.jpg.webp 2x">
    <source type="image/jpeg"
            sizes="(max-width: 767px) 300px, (min-width: 768px) 400px"
            srcset="/assets/w300-q90/folder/file.jpg 1x, /assets/w600-q90/folder/file.jpg 2x">
    <img alt="file.jpg"
        src="/assets/w300-q90/folder/file.jpg"
        srcset="/assets/w300-q90/folder/file.jpg 1x, /assets/w600-q90/folder/file.jpg 2x"
        sizes="(max-width: 767px) 300px, (min-width: 768px) 400px" />
</picture>
EOT
        );
        $this->assertHtmlTidyEquals(
            $renderer->render($mockDocument, [
                'fit' => '600x400',
                'srcset' => [[
                    'format' => [
                        'fit' => '600x400',
                    ],
                    'rule' => '1x'
                ], [
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
            ]),
            <<<EOT
<picture>
    <source type="image/webp"
            sizes="(max-width: 767px) 300px, (min-width: 768px) 400px"
            srcset="/assets/f600x400-q90/folder/file.jpg.webp 1x, /assets/f1200x800-q90/folder/file.jpg.webp 2x">
    <source type="image/jpeg"
            sizes="(max-width: 767px) 300px, (min-width: 768px) 400px"
            srcset="/assets/f600x400-q90/folder/file.jpg 1x, /assets/f1200x800-q90/folder/file.jpg 2x">
    <img alt="file.jpg"
        src="/assets/f600x400-q90/folder/file.jpg"
        srcset="/assets/f600x400-q90/folder/file.jpg 1x, /assets/f1200x800-q90/folder/file.jpg 2x"
        sizes="(max-width: 767px) 300px, (min-width: 768px) 400px"
        data-ratio="1.5" />
</picture>
EOT
        );

        $this->assertHtmlTidyEquals(
            $renderer->render($mockDocument, [
                'fit' => '600x400',
                'loading' => 'lazy',
                'srcset' => [[
                    'format' => [
                        'fit' => '600x400',
                    ],
                    'rule' => '1x'
                ], [
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
            ]),
            <<<EOT
<picture>
    <source type="image/webp"
            sizes="(max-width: 767px) 300px, (min-width: 768px) 400px"
            srcset="/assets/f600x400-q90/folder/file.jpg.webp 1x, /assets/f1200x800-q90/folder/file.jpg.webp 2x">
    <source type="image/jpeg"
            sizes="(max-width: 767px) 300px, (min-width: 768px) 400px"
            srcset="/assets/f600x400-q90/folder/file.jpg 1x, /assets/f1200x800-q90/folder/file.jpg 2x">
    <img alt="file.jpg"
        src="/assets/f600x400-q90/folder/file.jpg"
        srcset="/assets/f600x400-q90/folder/file.jpg 1x, /assets/f1200x800-q90/folder/file.jpg 2x"
        sizes="(max-width: 767px) 300px, (min-width: 768px) 400px"
        loading="lazy"
        data-ratio="1.5" />
</picture>
EOT
        );
        $this->assertHtmlTidyEquals(
            $renderer->render($mockDocument, [
                'fit' => '600x400',
                'lazyload' => true,
                'srcset' => [[
                    'format' => [
                        'fit' => '600x400',
                    ],
                    'rule' => '1x'
                ], [
                    'format' => [
                        'fit' => '1200x800',
                    ],
                    'rule' => '2x'
                ]],
                'picture' => true
            ]),
            <<<EOT
<picture>
    <source type="image/webp"
            srcset="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNcvGDBfwAGtQLk4581vAAAAABJRU5ErkJggg=="
            data-srcset="/assets/f600x400-q90/folder/file.jpg.webp 1x, /assets/f1200x800-q90/folder/file.jpg.webp 2x">
    <source type="image/jpeg"
            srcset="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNcvGDBfwAGtQLk4581vAAAAABJRU5ErkJggg=="
            data-srcset="/assets/f600x400-q90/folder/file.jpg 1x, /assets/f1200x800-q90/folder/file.jpg 2x">
    <img alt="file.jpg"
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
        <img alt="file.jpg"
             src="/assets/f600x400-q90/folder/file.jpg"
             srcset="/assets/f600x400-q90/folder/file.jpg 1x, /assets/f1200x800-q90/folder/file.jpg 2x"
             data-ratio="1.5"
             width="600"
             height="400" />
    </picture>
</noscript>
EOT
        );
        $this->assertHtmlTidyEquals(
            $renderer->render($mockDocument, [
                'fit' => '600x400',
                'lazyload' => true,
                'fallback' => 'https://test.test/fallback.png',
                'srcset' => [[
                    'format' => [
                        'fit' => '600x400',
                    ],
                    'rule' => '1x'
                ], [
                    'format' => [
                        'fit' => '1200x800',
                    ],
                    'rule' => '2x'
                ]],
                'picture' => true
            ]),
            <<<EOT
<picture>
    <source type="image/webp"
            srcset="https://test.test/fallback.png"
            data-srcset="/assets/f600x400-q90/folder/file.jpg.webp 1x, /assets/f1200x800-q90/folder/file.jpg.webp 2x">
    <source type="image/jpeg"
            srcset="https://test.test/fallback.png"
            data-srcset="/assets/f600x400-q90/folder/file.jpg 1x, /assets/f1200x800-q90/folder/file.jpg 2x">
    <img alt="file.jpg"
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
        <img alt="file.jpg"
            src="/assets/f600x400-q90/folder/file.jpg"
            srcset="/assets/f600x400-q90/folder/file.jpg 1x, /assets/f1200x800-q90/folder/file.jpg 2x"
            data-ratio="1.5"
            width="600"
            height="400" />
    </picture>
</noscript>
EOT
        );

        $this->assertHtmlTidyEquals(
            $renderer->render($mockDocument, [
                'fit' => '600x400',
                'media' => [[
                    'srcset' => [[
                        'format' => [
                            'fit' => '600x400',
                        ],
                        'rule' => '1x'
                    ], [
                        'format' => [
                            'fit' => '1200x800',
                        ],
                        'rule' => '2x'
                    ]],
                    'rule' => '(min-width: 600px)'
                ]],
                'picture' => true
            ]),
            <<<EOT
<picture>
    <source type="image/webp"
            media="(min-width: 600px)"
            srcset="/assets/f600x400-q90/folder/file.jpg.webp 1x, /assets/f1200x800-q90/folder/file.jpg.webp 2x">
    <source type="image/jpeg"
            media="(min-width: 600px)"
            srcset="/assets/f600x400-q90/folder/file.jpg 1x, /assets/f1200x800-q90/folder/file.jpg 2x">
    <img alt="file.jpg"
         src="/assets/f600x400-q90/folder/file.jpg"
         data-ratio="1.5"
         width="600"
         height="400" />
</picture>
EOT
        );

        $this->assertHtmlTidyEquals(
            $renderer->render($mockDocument, [
                'fit' => '600x400',
                'media' => [[
                    'srcset' => [[
                        'format' => [
                            'fit' => '600x400',
                        ],
                        'rule' => '1x'
                    ], [
                        'format' => [
                            'fit' => '1200x800',
                        ],
                        'rule' => '2x'
                    ]],
                    'rule' => '(min-width: 600px)'
                ], [
                    'srcset' => [[
                        'format' => [
                            'fit' => '1200x800',
                        ],
                        'rule' => '1x'
                    ], [
                        'format' => [
                            'fit' => '2400x1600',
                        ],
                        'rule' => '2x'
                    ]],
                    'rule' => '(min-width: 1200px)'
                ]],
                'picture' => true
            ]),
            <<<EOT
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

    <img alt="file.jpg"
         src="/assets/f600x400-q90/folder/file.jpg"
         data-ratio="1.5"
         width="600"
         height="400" />
</picture>
EOT
        );

        $this->assertHtmlTidyEquals(
            $renderer->render($mockDocument, [
                'fit' => '600x400',
                'loading' => 'lazy',
                'media' => [[
                    'srcset' => [[
                        'format' => [
                            'fit' => '600x400',
                        ],
                        'rule' => '1x'
                    ], [
                        'format' => [
                            'fit' => '1200x800',
                        ],
                        'rule' => '2x'
                    ]],
                    'rule' => '(min-width: 600px)'
                ], [
                    'srcset' => [[
                        'format' => [
                            'fit' => '1200x800',
                        ],
                        'rule' => '1x'
                    ], [
                        'format' => [
                            'fit' => '2400x1600',
                        ],
                        'rule' => '2x'
                    ]],
                    'rule' => '(min-width: 1200px)'
                ]],
                'picture' => true
            ]),
            <<<EOT
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

    <img alt="file.jpg"
         src="/assets/f600x400-q90/folder/file.jpg"
         loading="lazy"
         data-ratio="1.5"
         width="600"
         height="400" />
</picture>
EOT
        );
        $this->assertHtmlTidyEquals(
            $renderer->render($mockWebpDocument, [
                'fit' => '600x400',
                'lazyload' => true,
                'fallback' => 'FALLBACK',
                'media' => [[
                    'srcset' => [[
                        'format' => [
                            'fit' => '600x400',
                        ],
                        'rule' => '1x'
                    ], [
                        'format' => [
                            'fit' => '1200x800',
                        ],
                        'rule' => '2x'
                    ]],
                    'rule' => '(min-width: 600px)'
                ], [
                    'srcset' => [[
                        'format' => [
                            'fit' => '1200x800',
                        ],
                        'rule' => '1x'
                    ], [
                        'format' => [
                            'fit' => '2400x1600',
                        ],
                        'rule' => '2x'
                    ]],
                    'rule' => '(min-width: 1200px)'
                ]],
                'picture' => true
            ]),
            <<<EOT
<picture>
    <source type="image/webp"
            media="(min-width: 600px)"
            srcset="FALLBACK"
            data-srcset="/assets/f600x400-q90/folder/file.webp 1x, /assets/f1200x800-q90/folder/file.webp 2x">

    <source type="image/webp"
            media="(min-width: 1200px)"
            srcset="FALLBACK"
            data-srcset="/assets/f1200x800-q90/folder/file.webp 1x, /assets/f2400x1600-q90/folder/file.webp 2x">

    <img alt="file.webp"
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

        <img alt="file.webp"
             src="/assets/f600x400-q90/folder/file.webp"
             data-ratio="1.5"
             width="600"
             height="400" />
    </picture>
</noscript>
EOT
        );
        $this->assertHtmlTidyEquals(
            $renderer->render($mockWebpDocument, [
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
                    ], [
                        'format' => [
                            'fit' => '1200x800',
                        ],
                        'rule' => '2x'
                    ]],
                    'rule' => '(min-width: 600px)'
                ], [
                    'srcset' => [[
                        'format' => [
                            'fit' => '1200x800',
                        ],
                        'rule' => '1x'
                    ], [
                        'format' => [
                            'fit' => '2400x1600',
                        ],
                        'rule' => '2x'
                    ]],
                    'rule' => '(min-width: 1200px)'
                ]],
                'picture' => true
            ]),
            <<<EOT
<picture>
    <source type="image/webp"
            media="(min-width: 600px)"
            srcset="FALLBACK"
            data-srcset="/assets/f600x400-q90/folder/file.webp 1x, /assets/f1200x800-q90/folder/file.webp 2x">

    <source type="image/webp"
            media="(min-width: 1200px)"
            srcset="FALLBACK"
            data-srcset="/assets/f1200x800-q90/folder/file.webp 1x, /assets/f2400x1600-q90/folder/file.webp 2x">

    <img alt="file.webp"
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

        <img alt="file.webp"
             src="/assets/f600x400-q90/folder/file.webp"
             loading="lazy"
             data-ratio="1.5"
             width="600"
             height="400" />
    </picture>
</noscript>
EOT
        );
    }
}
