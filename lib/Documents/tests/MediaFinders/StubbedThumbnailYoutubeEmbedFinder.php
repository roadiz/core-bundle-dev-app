<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Tests\MediaFinders;

use Doctrine\Persistence\ObjectManager;
use RZ\Roadiz\Documents\MediaFinders\AbstractYoutubeEmbedFinder;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Test finder that stubs the oEmbed thumbnail URL so the maxres derivation can
 * be asserted without hitting the network.
 */
final class StubbedThumbnailYoutubeEmbedFinder extends AbstractYoutubeEmbedFinder
{
    public function __construct(
        HttpClientInterface $client,
        string $embedId,
        private readonly string $thumbnailUrl,
    ) {
        parent::__construct($client, $embedId);
    }

    public function getThumbnailURL(): string
    {
        return $this->thumbnailUrl;
    }

    public function exposeMaxResThumbnailURL(): ?string
    {
        return $this->getMaxResThumbnailURL();
    }

    protected function documentExists(ObjectManager $objectManager, string $embedId, ?string $embedPlatform): bool
    {
        throw new \RuntimeException('Not implemented');
    }

    protected function injectMetaInDocument(ObjectManager $objectManager, DocumentInterface $document): DocumentInterface
    {
        throw new \RuntimeException('Not implemented');
    }

    protected function getExistingDocument(ObjectManager $objectManager, string $embedId, ?string $embedPlatform): ?DocumentInterface
    {
        throw new \RuntimeException('Not implemented');
    }
}
