<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\MediaFinders;

interface EmbedFinderInterface
{
    /**
     * @param array $options
     *
     * @return string
     */
    public function getIFrame(array &$options = []): string;

    /**
     * @param array $options
     *
     * @return string
     */
    public function getSource(array &$options = []): string;

    public static function supportEmbedUrl(string $embedUrl): bool;

    public static function getPlatform(): string;

    /**
     * @return string Embed short type for displaying icons
     */
    public function getShortType(): string;
}
