<?php

declare(strict_types=1);

namespace RZ\Roadiz\Markdown;

use League\CommonMark\MarkdownConverter;
use Symfony\Component\Stopwatch\Stopwatch;

final readonly class CommonMark implements MarkdownInterface
{
    public function __construct(
        private MarkdownConverter $textConverter,
        private MarkdownConverter $textExtraConverter,
        private MarkdownConverter $lineConverter,
        private MarkdownConverter $textHtmlConverter,
        private MarkdownConverter $textExtraHtmlConverter,
        private ?Stopwatch $stopwatch = null,
    ) {
    }

    #[\Override]
    public function text(?string $markdown = null, bool $allowHtml = false): string
    {
        if (null === $markdown) {
            return '';
        }
        $this->stopwatch?->start(CommonMark::class.'::text');
        $converter = $allowHtml ? $this->textHtmlConverter : $this->textConverter;
        $html = $converter->convert($markdown)->getContent();
        $this->stopwatch?->stop(CommonMark::class.'::text');

        return $html;
    }

    #[\Override]
    public function textExtra(?string $markdown = null, bool $allowHtml = false): string
    {
        if (null === $markdown) {
            return '';
        }
        $this->stopwatch?->start(CommonMark::class.'::textExtra');
        $converter = $allowHtml ? $this->textExtraHtmlConverter : $this->textExtraConverter;
        $html = $converter->convert($markdown)->getContent();
        $this->stopwatch?->stop(CommonMark::class.'::textExtra');

        return $html;
    }

    #[\Override]
    public function line(?string $markdown = null): string
    {
        if (null === $markdown) {
            return '';
        }
        $this->stopwatch?->start(CommonMark::class.'::line');
        $html = $this->lineConverter->convert($markdown)->getContent();
        $this->stopwatch?->stop(CommonMark::class.'::line');

        return $html;
    }
}
