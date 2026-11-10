<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\MediaFinders;

use Doctrine\Persistence\ObjectManager;
use RZ\Roadiz\Documents\AbstractDocumentFactory;
use RZ\Roadiz\Documents\Models\DocumentInterface;

interface EmbedFinderInterface
{
    public function getIFrame(array &$options = []): string;

    public function getSource(array &$options = []): string;

    /**
     * Get the original public media URL (permalink), not the iframe/embed source.
     *
     * @return string|null Null when the platform has no public URI to build back
     */
    public function getPublicUri(): ?string;

    public static function supportEmbedUrl(string $embedUrl): bool;

    public static function getPlatform(): string;

    /**
     * @return string Embed short type for displaying icons
     */
    public function getShortType(): string;

    public function createDocumentFromFeed(
        ObjectManager $objectManager,
        AbstractDocumentFactory $documentFactory,
    ): DocumentInterface|array;
}
