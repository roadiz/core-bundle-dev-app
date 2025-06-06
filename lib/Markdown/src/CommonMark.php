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
        private ?Stopwatch $stopwatch = null,
    ) {
    }

    #[\Override]
    public function text(?string $markdown = null): string
    {
        if (null === $markdown) {
            return '';
        }
        $this->stopwatch?->start(CommonMark::class.'::text');
        $html = $this->textConverter->convert($markdown)->getContent();
        $this->stopwatch?->stop(CommonMark::class.'::text');

        return $html;
    }

    #[\Override]
    public function textExtra(?string $markdown = null): string
    {
        if (null === $markdown) {
            return '';
        }
        $this->stopwatch?->start(CommonMark::class.'::textExtra');
        $html = $this->textExtraConverter->convert($markdown)->getContent();
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
