<?php
declare(strict_types=1);

namespace RZ\Roadiz\Documents\Renderer\tests\units;

use atoum;
use RZ\Roadiz\Documents\MediaFinders\EmbedFinderFactory;
use RZ\Roadiz\Documents\Models\DocumentInterface;

class EmbedRenderer extends atoum
{
    public function testSupports()
    {
        /** @var DocumentInterface $mockValidDocument */
        $mockValidDocument = new \mock\RZ\Roadiz\Documents\Models\SimpleDocument();
        $mockValidDocument->setFilename('poster.jpg');
        $mockValidDocument->setEmbedId('xxxxxxx');
        $mockValidDocument->setEmbedPlatform('youtube');
        $mockValidDocument->setMimeType('image/jpeg');

        /** @var DocumentInterface $mockInvalidDocument */
        $mockInvalidDocument = new \mock\RZ\Roadiz\Documents\Models\SimpleDocument();
        $mockInvalidDocument->setFilename('file.jpg');
        $mockInvalidDocument->setMimeType('image/jpeg');

        /** @var DocumentInterface $mockExternalInvalidDocument */
        $mockExternalInvalidDocument = new \mock\RZ\Roadiz\Documents\Models\SimpleDocument();
        $mockExternalInvalidDocument->setFilename('file.jpg');
        $mockExternalInvalidDocument->setMimeType('image/jpeg');
        $mockExternalInvalidDocument->setEmbedId('xxxxx');
        $mockExternalInvalidDocument->setEmbedPlatform('getty');

        $this
            ->given($renderer = $this->newTestedInstance(
                $this->getEmbedFinderFactory()
            ))
            ->then
            ->boolean($mockValidDocument->isEmbed())
            ->isEqualTo(true)
            ->boolean($renderer->supports($mockValidDocument, []))
            ->isEqualTo(false)
            ->boolean($renderer->supports($mockValidDocument, ['embed' => true]))
            ->isEqualTo(true)
            ->boolean($renderer->supports($mockExternalInvalidDocument, ['embed' => true]))
            ->isEqualTo(false)
            ->boolean($mockInvalidDocument->isEmbed())
            ->isEqualTo(false)
            ->boolean($renderer->supports($mockInvalidDocument, []))
            ->isEqualTo(false)
            ->boolean($renderer->supports($mockInvalidDocument, ['embed' => true]))
            ->isEqualTo(false)
        ;
    }

    public function testRender()
    {
        /** @var DocumentInterface $mockDocumentYoutube */
        $mockDocumentYoutube = new \mock\RZ\Roadiz\Documents\Models\SimpleDocument();
        $mockDocumentYoutube->setFilename('poster.jpg');
        $mockDocumentYoutube->setEmbedId('xxxxxxx');
        $mockDocumentYoutube->setEmbedPlatform('youtube');
        $mockDocumentYoutube->setMimeType('image/jpeg');

        /** @var DocumentInterface $mockDocumentVimeo */
        $mockDocumentVimeo = new \mock\RZ\Roadiz\Documents\Models\SimpleDocument();
        $mockDocumentVimeo->setFilename('poster.jpg');
        $mockDocumentVimeo->setEmbedId('0000000');
        $mockDocumentVimeo->setEmbedPlatform('vimeo');
        $mockDocumentVimeo->setMimeType('image/jpeg');

        $this
            ->given($renderer = $this->newTestedInstance(
                $this->getEmbedFinderFactory()
            ))
            ->then
            ->boolean($mockDocumentYoutube->isEmbed())
            ->isEqualTo(true)
            ->string($renderer->render($mockDocumentYoutube, ['embed' => true]))
            ->isEqualTo($this->htmlTidy(<<<EOT
<iframe src="https://www.youtube-nocookie.com/embed/xxxxxxx?rel=0&html5=1&wmode=transparent&loop=0&controls=1&fs=1&modestbranding=1&showinfo=0&enablejsapi=1&mute=0"
        allow="accelerometer; encrypted-media; gyroscope; picture-in-picture; fullscreen"
        allowFullScreen></iframe>
EOT
            ))
            ->string($renderer->render($mockDocumentYoutube, [
                'embed' => true,
                'loading' => 'lazy'
            ]))
            ->isEqualTo($this->htmlTidy(<<<EOT
<iframe src="https://www.youtube-nocookie.com/embed/xxxxxxx?rel=0&html5=1&wmode=transparent&loop=0&controls=1&fs=1&modestbranding=1&showinfo=0&enablejsapi=1&mute=0"
        allow="accelerometer; encrypted-media; gyroscope; picture-in-picture; fullscreen"
        allowFullScreen
        loading="lazy"></iframe>
EOT
            ))
            ->string($renderer->render($mockDocumentYoutube, [
                'embed' => true,
                'width' => 500
                // height is auto calculated based on 16/10 ratio
            ]))
            ->isEqualTo($this->htmlTidy(<<<EOT
<iframe src="https://www.youtube-nocookie.com/embed/xxxxxxx?rel=0&html5=1&wmode=transparent&loop=0&controls=1&fs=1&modestbranding=1&showinfo=0&enablejsapi=1&mute=0"
        allow="accelerometer; encrypted-media; gyroscope; picture-in-picture; fullscreen"
        width="500" height="312" allowFullScreen></iframe>
EOT
            ))
            ->string($renderer->render($mockDocumentYoutube, [
                'embed' => true,
                'width' => 500,
                'height' => 500
            ]))
            ->isEqualTo($this->htmlTidy(<<<EOT
<iframe src="https://www.youtube-nocookie.com/embed/xxxxxxx?rel=0&html5=1&wmode=transparent&loop=0&controls=1&fs=1&modestbranding=1&showinfo=0&enablejsapi=1&mute=0"
        allow="accelerometer; encrypted-media; gyroscope; picture-in-picture; fullscreen"
        width="500" height="500" allowFullScreen></iframe>
EOT
            ))
            ->string($renderer->render($mockDocumentYoutube, [
                'embed' => true,
                'autoplay' => true,
            ]))
            ->isEqualTo($this->htmlTidy(<<<EOT
<iframe src="https://www.youtube-nocookie.com/embed/xxxxxxx?rel=0&html5=1&wmode=transparent&autoplay=1&playsinline=1&loop=0&controls=1&fs=1&modestbranding=1&showinfo=0&enablejsapi=1&mute=0"
        allow="accelerometer; encrypted-media; gyroscope; picture-in-picture; autoplay; fullscreen"
        allowFullScreen></iframe>
EOT
            ))
            ->boolean($mockDocumentVimeo->isEmbed())
            ->isEqualTo(true)
            ->string($renderer->render($mockDocumentVimeo, ['embed' => true]))
            ->isEqualTo($this->htmlTidy(<<<EOT
<iframe src="https://player.vimeo.com/video/0000000?title=0&byline=0&portrait=0&api=1&loop=0&fullscreen=1&controls=1&autopause=0&automute=0"
        allow="accelerometer; encrypted-media; gyroscope; picture-in-picture; fullscreen"
        allowFullScreen></iframe>
EOT
            ))
            ->string($renderer->render($mockDocumentVimeo, [
                'embed' => true,
                'autoplay' => true,
                'automute' => true,
            ]))
            ->isEqualTo($this->htmlTidy(<<<EOT
<iframe src="https://player.vimeo.com/video/0000000?title=0&byline=0&portrait=0&api=1&loop=0&fullscreen=1&controls=1&autopause=0&automute=1&autoplay=1&playsinline=1"
        allow="accelerometer; encrypted-media; gyroscope; picture-in-picture; autoplay; fullscreen"
        allowFullScreen></iframe>
EOT
            ))
            ->string($renderer->render($mockDocumentVimeo, [
                'embed' => true,
                'autoplay' => true,
                'background' => "1", // Hack background conflict option with background color
            ]))
            ->isEqualTo($this->htmlTidy(<<<EOT
<iframe src="https://player.vimeo.com/video/0000000?title=0&byline=0&portrait=0&api=1&loop=0&fullscreen=1&controls=1&autopause=0&automute=0&autoplay=1&playsinline=1&background=1"
        allow="accelerometer; encrypted-media; gyroscope; picture-in-picture; autoplay; fullscreen"
        allowFullScreen></iframe>
EOT
            ))
        ;
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

    private function htmlTidy(string $body): string
    {
        $body = preg_replace('#[\n\r\t\s]{2,}#', ' ', $body);
        return preg_replace('#\>[\n\r\t\s]+\<#', '><', $body);
    }
}
