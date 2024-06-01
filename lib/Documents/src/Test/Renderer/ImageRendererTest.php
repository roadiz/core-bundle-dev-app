<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Test\Renderer;

use RZ\Roadiz\Documents\Models\SimpleDocument;
use RZ\Roadiz\Documents\Renderer\ImageRenderer;

class ImageRendererTest extends AbstractRendererTestCase
{
    protected function getRenderer(): ImageRenderer
    {
        return new ImageRenderer(
            $this->getFilesystemOperator(),
            $this->getEmbedFinderFactory(),
            $this->getEnvironment(),
            $this->getUrlGenerator()
        );
    }

    public function testSupports(): void
    {
        $mockValidDocument = new SimpleDocument();
        $mockValidDocument->setFilename('file.jpg');
        $mockValidDocument->setMimeType('image/jpeg');

        $mockExternalValidDocument = new SimpleDocument();
        $mockExternalValidDocument->setFilename('file.jpg');
        $mockExternalValidDocument->setMimeType('image/jpeg');
        $mockExternalValidDocument->setEmbedId('xxxxx');
        $mockExternalValidDocument->setEmbedPlatform('getty');

        $mockInvalidDocument = new SimpleDocument();
        $mockInvalidDocument->setFilename('file.psd');
        $mockInvalidDocument->setMimeType('image/vnd.adobe.photoshop');

        $renderer = $this->getRenderer();

        $this->assertIsString($mockValidDocument->getMimeType());
        $this->assertEquals(
            'image/jpeg',
            $mockValidDocument->getMimeType()
        );
        $this->assertTrue($renderer->supports($mockValidDocument, []));
        $this->assertTrue($renderer->supports($mockValidDocument, ['embed' => true]));
        $this->assertTrue($renderer->supports($mockExternalValidDocument, ['embed' => true]));
        $this->assertFalse($renderer->supports($mockValidDocument, ['picture' => true]));
        $this->assertEquals(
            'image/vnd.adobe.photoshop',
            $mockInvalidDocument->getMimeType()
        );
        $this->assertFalse($renderer->supports($mockInvalidDocument, []));
    }

    public function testRender(): void
    {
        $mockDocument = new SimpleDocument();
        $mockDocument->setFilename('file.jpg');
        $mockDocument->setFolder('folder');
        $mockDocument->setMimeType('image/jpeg');

        $renderer = $this->getRenderer();

        $this->assertHtmlTidyEquals(
            <<<EOT
<img alt="file.jpg" src="/files/folder/file.jpg" />
EOT,
            $renderer->render($mockDocument, ['noProcess' => true])
        );

        $this->assertHtmlTidyEquals(
            <<<EOT
<img alt="file.jpg" src="http://dummy.test/files/folder/file.jpg" />
EOT,
            $renderer->render($mockDocument, ['absolute' => true, 'noProcess' => true])
        );

        $this->assertHtmlTidyEquals(
            <<<EOT
<img alt="file.jpg" src="http://dummy.test/assets/w300-q90/folder/file.jpg" width="300" />
EOT,
            $renderer->render($mockDocument, [
                'width' => 300,
                'absolute' => true
            ])
        );

        $this->assertHtmlTidyEquals(
            <<<EOT
<img alt="file.jpg"
    src="http://dummy.test/assets/w300-q90/folder/file.jpg"
    width="300"
    class="awesome-image responsive" />
EOT,
            $renderer->render($mockDocument, [
                'width' => 300,
                'class' => 'awesome-image responsive',
                'absolute' => true
            ])
        );

        $this->assertHtmlTidyEquals(
            <<<EOT
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
EOT,
            $renderer->render($mockDocument, [
                'width' => 300,
                'lazyload' => true
            ])
        );

        $this->assertHtmlTidyEquals(
            <<<EOT
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
EOT,
            $renderer->render($mockDocument, [
                'width' => 300,
                'lazyload' => true,
                'fallback' => 'https://test.test/fallback.png'
            ])
        );

        $this->assertHtmlTidyEquals(
            <<<EOT
<img alt="file.jpg"
    src="/assets/w300-q90/folder/file.jpg"
    width="300" />
EOT,
            $renderer->render($mockDocument, [
                'width' => 300,
                'fallback' => 'https://test.test/fallback.png'
            ])
        );

        $this->assertHtmlTidyEquals(
            <<<EOT
<img alt="file.jpg"
     src="/assets/f600x400-q70/folder/file.jpg"
     data-ratio="1.5"
     width="600"
     height="400" />
EOT,
            $renderer->render($mockDocument, [
                'fit' => '600x400',
                'quality' => 70
            ])
        );

        $this->assertHtmlTidyEquals(
            <<<EOT
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
EOT,
            $renderer->render($mockDocument, [
                'width' => 300,
                'lazyload' => true,
                'class' => 'awesome-image responsive',
            ])
        );

        $this->assertHtmlTidyEquals(
            <<<EOT
<img alt="file.jpg"
     src="/assets/w300-q90/folder/file.jpg"
     srcset="/assets/w300-q90/folder/file.jpg 1x, /assets/w600-q90/folder/file.jpg 2x"
     width="300" />
EOT,
            $renderer->render($mockDocument, [
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
            ])
        );

        $this->assertHtmlTidyEquals(
            <<<EOT
<img alt="file.jpg"
     src="/assets/w300-q90/folder/file.jpg"
     srcset="/assets/w300-q90/folder/file.jpg 1x, /assets/w600-q90/folder/file.jpg 2x"
     sizes="(max-width: 767px) 300px, (min-width: 768px) 400px" />
EOT,
            $renderer->render($mockDocument, [
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
            ])
        );

        $this->assertHtmlTidyEquals(
            <<<EOT
<img alt="file.jpg"
     src="/assets/f600x400-q90/folder/file.jpg"
     srcset="/assets/f600x400-q90/folder/file.jpg 1x, /assets/f1200x800-q90/folder/file.jpg 2x"
     sizes="(max-width: 767px) 300px, (min-width: 768px) 400px"
     data-ratio="1.5" />
EOT,
            $renderer->render($mockDocument, [
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
            ])
        );

        $this->assertHtmlTidyEquals(
            <<<EOT
<img alt="file.jpg"
     src="/assets/f600x400-q90/folder/file.jpg"
     srcset="/assets/f600x400-q90/folder/file.jpg 1x, /assets/f1200x800-q90/folder/file.jpg 2x"
     sizes="(max-width: 767px) 300px, (min-width: 768px) 400px"
     loading="lazy"
     data-ratio="1.5" />
EOT,
            $renderer->render($mockDocument, [
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
            ])
        );

        $this->assertHtmlTidyEquals(
            <<<EOT
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
EOT,
            $renderer->render($mockDocument, [
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
            ])
        );

        $this->assertHtmlTidyEquals(
            <<<EOT
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
EOT,
            $renderer->render($mockDocument, [
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
            ])
        );
    }
}
