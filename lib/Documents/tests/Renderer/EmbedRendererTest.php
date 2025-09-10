<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Tests\Renderer;

use RZ\Roadiz\Documents\Models\SimpleDocument;
use RZ\Roadiz\Documents\Renderer\EmbedRenderer;

class EmbedRendererTest extends AbstractRendererTestCase
{
    protected function getRenderer(): EmbedRenderer
    {
        return new EmbedRenderer($this->getEmbedFinderFactory());
    }

    public function testSupports(): void
    {
        $mockValidDocument = new SimpleDocument();
        $mockValidDocument->setFilename('poster.jpg');
        $mockValidDocument->setEmbedId('xxxxxxx');
        $mockValidDocument->setEmbedPlatform('youtube');
        $mockValidDocument->setMimeType('image/jpeg');

        $mockInvalidDocument = new SimpleDocument();
        $mockInvalidDocument->setFilename('file.jpg');
        $mockInvalidDocument->setMimeType('image/jpeg');

        $mockExternalInvalidDocument = new SimpleDocument();
        $mockExternalInvalidDocument->setFilename('file.jpg');
        $mockExternalInvalidDocument->setMimeType('image/jpeg');
        $mockExternalInvalidDocument->setEmbedId('xxxxx');
        $mockExternalInvalidDocument->setEmbedPlatform('getty');

        $renderer = $this->getRenderer();

        $this->assertIsBool($mockValidDocument->isEmbed());
        $this->assertTrue($mockValidDocument->isEmbed());

        $this->assertFalse($renderer->supports($mockValidDocument, []));
        $this->assertTrue($renderer->supports($mockValidDocument, ['embed' => true]));
        $this->assertFalse($renderer->supports($mockExternalInvalidDocument, ['embed' => true]));
        $this->assertFalse($mockInvalidDocument->isEmbed());
        $this->assertFalse($renderer->supports($mockInvalidDocument, []));
        $this->assertFalse($renderer->supports($mockInvalidDocument, ['embed' => true]));
    }

    public function testRender(): void
    {
        $mockDocumentYoutube = new SimpleDocument();
        $mockDocumentYoutube->setFilename('poster.jpg');
        $mockDocumentYoutube->setEmbedId('xxxxxxx');
        $mockDocumentYoutube->setEmbedPlatform('youtube');
        $mockDocumentYoutube->setMimeType('image/jpeg');

        $mockDocumentVimeo = new SimpleDocument();
        $mockDocumentVimeo->setFilename('poster.jpg');
        $mockDocumentVimeo->setEmbedId('0000000');
        $mockDocumentVimeo->setEmbedPlatform('vimeo');
        $mockDocumentVimeo->setMimeType('image/jpeg');

        $renderer = $this->getRenderer();

        $this->assertIsBool($mockDocumentYoutube->isEmbed());
        $this->assertTrue($mockDocumentYoutube->isEmbed());

        $this->assertHtmlTidyEquals(
            <<<EOT
<iframe src="https://www.youtube-nocookie.com/embed/xxxxxxx?rel=0&html5=1&wmode=transparent&loop=0&controls=1&fs=1&modestbranding=1&showinfo=0&enablejsapi=1&mute=0"
        allow="accelerometer; encrypted-media; gyroscope; picture-in-picture; fullscreen"
        allowFullScreen></iframe>
EOT,
            $renderer->render($mockDocumentYoutube, ['embed' => true])
        );

        $this->assertHtmlTidyEquals(
            <<<EOT
<iframe src="https://www.youtube-nocookie.com/embed/xxxxxxx?rel=0&html5=1&wmode=transparent&loop=0&controls=1&fs=1&modestbranding=1&showinfo=0&enablejsapi=1&mute=0"
        allow="accelerometer; encrypted-media; gyroscope; picture-in-picture; fullscreen"
        allowFullScreen
        loading="lazy"></iframe>
EOT,
            $renderer->render($mockDocumentYoutube, [
                'embed' => true,
                'loading' => 'lazy',
            ])
        );

        $this->assertHtmlTidyEquals(
            <<<EOT
<iframe src="https://www.youtube-nocookie.com/embed/xxxxxxx?rel=0&html5=1&wmode=transparent&loop=0&controls=1&fs=1&modestbranding=1&showinfo=0&enablejsapi=1&mute=0"
        allow="accelerometer; encrypted-media; gyroscope; picture-in-picture; fullscreen"
        width="500" height="312" allowFullScreen></iframe>
EOT,
            $renderer->render($mockDocumentYoutube, [
                'embed' => true,
                'width' => 500,
                // height is auto calculated based on 16/10 ratio
            ])
        );

        $this->assertHtmlTidyEquals(
            <<<EOT
<iframe src="https://www.youtube-nocookie.com/embed/xxxxxxx?rel=0&html5=1&wmode=transparent&loop=0&controls=1&fs=1&modestbranding=1&showinfo=0&enablejsapi=1&mute=0"
        allow="accelerometer; encrypted-media; gyroscope; picture-in-picture; fullscreen"
        width="500" height="500" allowFullScreen></iframe>
EOT,
            $renderer->render($mockDocumentYoutube, [
                'embed' => true,
                'width' => 500,
                'height' => 500,
            ])
        );

        $this->assertHtmlTidyEquals(
            <<<EOT
<iframe src="https://www.youtube-nocookie.com/embed/xxxxxxx?rel=0&html5=1&wmode=transparent&autoplay=1&playsinline=1&loop=0&controls=1&fs=1&modestbranding=1&showinfo=0&enablejsapi=1&mute=0"
        allow="accelerometer; encrypted-media; gyroscope; picture-in-picture; autoplay; fullscreen"
        allowFullScreen></iframe>
EOT,
            $renderer->render($mockDocumentYoutube, [
                'embed' => true,
                'autoplay' => true,
            ])
        );

        $this->assertIsBool($mockDocumentVimeo->isEmbed());
        $this->assertTrue($mockDocumentVimeo->isEmbed());

        $this->assertHtmlTidyEquals(
            <<<EOT
<iframe src="https://player.vimeo.com/video/0000000?title=0&byline=0&portrait=0&api=1&loop=0&fullscreen=1&controls=1&autopause=0&automute=0"
        allow="accelerometer; encrypted-media; gyroscope; picture-in-picture; fullscreen"
        allowFullScreen></iframe>
EOT,
            $renderer->render($mockDocumentVimeo, ['embed' => true])
        );

        $this->assertHtmlTidyEquals(
            <<<EOT
<iframe src="https://player.vimeo.com/video/0000000?title=0&byline=0&portrait=0&api=1&loop=0&fullscreen=1&controls=1&autopause=0&automute=1&autoplay=1&playsinline=1"
        allow="accelerometer; encrypted-media; gyroscope; picture-in-picture; autoplay; fullscreen"
        allowFullScreen></iframe>
EOT,
            $renderer->render($mockDocumentVimeo, [
                'embed' => true,
                'autoplay' => true,
                'automute' => true,
            ])
        );

        $this->assertHtmlTidyEquals(
            <<<EOT
<iframe src="https://player.vimeo.com/video/0000000?title=0&byline=0&portrait=0&api=1&loop=0&fullscreen=1&controls=1&autopause=0&automute=0&autoplay=1&playsinline=1&background=1"
        allow="accelerometer; encrypted-media; gyroscope; picture-in-picture; autoplay; fullscreen"
        allowFullScreen></iframe>
EOT,
            $renderer->render($mockDocumentVimeo, [
                'embed' => true,
                'autoplay' => true,
                'background' => '1', // Hack background conflict option with background color
            ])
        );
    }
}
