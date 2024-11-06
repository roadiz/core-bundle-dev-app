<?php

declare(strict_types=1);

namespace RZ\Roadiz\Markdown;

interface MarkdownInterface
{
    /**
     * Convert Markdown to HTML using standard Markdown syntax.
     */
    public function text(?string $markdown = null): string;

    /**
     * Convert Markdown to HTML using standard Markdown Extra syntax.
     */
    public function textExtra(?string $markdown = null): string;

    /**
     * Convert Markdown to HTML using only inline HTML elements.
     */
    public function line(?string $markdown = null): string;
}
