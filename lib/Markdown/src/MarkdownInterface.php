<?php

declare(strict_types=1);

namespace RZ\Roadiz\Markdown;

interface MarkdownInterface
{
    /**
     * Convert Markdown to HTML using standard Markdown syntax.
     *
     * @param string|null $markdown
     *
     * @return string
     */
    public function text(string $markdown = null): string;

    /**
     * Convert Markdown to HTML using standard Markdown Extra syntax.
     *
     * @param string|null $markdown
     *
     * @return string
     */
    public function textExtra(string $markdown = null): string;

    /**
     * Convert Markdown to HTML using only inline HTML elements.
     *
     * @param string|null $markdown
     *
     * @return string
     */
    public function line(string $markdown = null): string;
}
