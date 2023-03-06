<?php

declare(strict_types=1);

namespace RZ\Roadiz\Markdown\Twig;

use RZ\Roadiz\Markdown\MarkdownInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class MarkdownExtension extends AbstractExtension
{
    private MarkdownInterface $markdown;

    public function __construct(MarkdownInterface $markdown)
    {
        $this->markdown = $markdown;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('markdown', [$this, 'markdown'], ['is_safe' => ['html']]),
            new TwigFilter('inlineMarkdown', [$this, 'inlineMarkdown'], ['is_safe' => ['html']]),
            new TwigFilter('inline_markdown', [$this, 'inlineMarkdown'], ['is_safe' => ['html']]),
            new TwigFilter('markdownExtra', [$this, 'markdownExtra'], ['is_safe' => ['html']]),
            new TwigFilter('markdown_extra', [$this, 'markdownExtra'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param string|null $input
     *
     * @return string
     */
    public function markdown(?string $input): string
    {
        if (null === $input) {
            return '';
        }
        return $this->markdown->text($input);
    }

    /**
     * @param string|null $input
     *
     * @return string
     */
    public function inlineMarkdown(?string $input): string
    {
        if (null === $input) {
            return '';
        }
        return $this->markdown->line($input);
    }

    /**
     * @param string|null $input
     *
     * @return string
     */
    public function markdownExtra(?string $input): string
    {
        if (null === $input) {
            return '';
        }
        return $this->markdown->textExtra($input);
    }
}
