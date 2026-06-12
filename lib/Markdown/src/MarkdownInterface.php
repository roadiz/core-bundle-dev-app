<?php

declare(strict_types=1);

namespace RZ\Roadiz\Markdown;

interface MarkdownInterface
{
    /**
     * Convert Markdown to HTML using standard Markdown syntax.
     *
     * @param bool $allowHtml Pass true to allow raw HTML (including script/style tags) through
     *                        unchanged. Defaults to false (raw HTML is stripped).
     */
    public function text(?string $markdown = null, bool $allowHtml = false): string;

    /**
     * Convert Markdown to HTML using standard Markdown Extra syntax.
     *
     * @param bool $allowHtml Pass true to allow raw HTML (including script/style tags) through
     *                        unchanged. Defaults to false (raw HTML is stripped).
     */
    public function textExtra(?string $markdown = null, bool $allowHtml = false): string;

    /**
     * Convert Markdown to HTML using only inline HTML elements.
     */
    public function line(?string $markdown = null): string;
}
