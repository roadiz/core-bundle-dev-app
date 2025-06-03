<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\MediaFinders;

use Doctrine\Persistence\ObjectManager;
use League\Flysystem\FilesystemException;
use RZ\Roadiz\Documents\AbstractDocumentFactory;
use RZ\Roadiz\Documents\DownloadedFile;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use RZ\Roadiz\Documents\Models\TimeableInterface;

abstract class AbstractPodcastFinder extends AbstractEmbedFinder
{
    #[\Override]
    public static function supportEmbedUrl(string $embedUrl): bool
    {
        return false;
    }

    #[\Override]
    public static function getPlatform(): string
    {
        return 'podcast';
    }

    #[\Override]
    protected function validateEmbedId(string $embedId = ''): string
    {
        return $embedId;
    }

    #[\Override]
    public function getFeed(): array|\SimpleXMLElement|null
    {
        if (null === $this->feed) {
            $rawFeed = $this->getMediaFeed();
            try {
                $this->feed = new \SimpleXMLElement($rawFeed);

                return $this->feed;
            } catch (\Exception) {
                throw new \RuntimeException('Feed content is not a valid Podcast XML');
            }
        }

        return $this->feed;
    }

    protected function getAudioName(\SimpleXMLElement $item): string
    {
        if (null !== $item->enclosure->attributes()) {
            $url = (string) $item->enclosure->attributes()->url;
        } else {
            throw new \RuntimeException('Podcast element does not have any enclosure URL.');
        }

        if (!empty((string) $item->title)) {
            $extension = pathinfo($url, PATHINFO_EXTENSION);

            return ((string) $item->title).'.'.$extension;
        }

        return pathinfo($url, PATHINFO_BASENAME);
    }

    /**
     * Create a Document from an embed media.
     *
     * Be careful, this method does not flush.
     *
     * @return array<DocumentInterface>
     *
     * @throws FilesystemException
     */
    #[\Override]
    public function createDocumentFromFeed(
        ObjectManager $objectManager,
        AbstractDocumentFactory $documentFactory,
    ): array {
        $documents = [];
        $feed = $this->getFeed();
        if ($feed instanceof \SimpleXMLElement) {
            foreach ($feed->channel->item as $item) {
                if (
                    !empty($item->enclosure->attributes()->url)
                    && !$this->documentExists($objectManager, $item->guid->__toString(), null)
                ) {
                    $podcastUrl = (string) $item->enclosure->attributes()->url;
                    $thumbnailName = $this->getAudioName($item);
                    $file = DownloadedFile::fromUrl($podcastUrl, $thumbnailName);
                    $namespaces = $item->getNameSpaces(true);
                    $itunes = $item->children($namespaces['itunes']);

                    if (null !== $file) {
                        $documentFactory->setFile($file);
                        $document = $documentFactory->getDocument(false, $this->areDuplicatesAllowed());
                        if (null !== $document) {
                            /*
                             * Create document metas
                             * for each translation
                             */
                            $this->injectMetaFromPodcastItem($objectManager, $document, $item);
                            $document->setEmbedId((string) $item->guid);
                            $document->setEmbedPlatform(null);

                            if ($document instanceof TimeableInterface && !empty((string) $itunes->duration)) {
                                if (
                                    preg_match(
                                        '#([0-9]+)\:([0-9]+)\:([0-9]+)#',
                                        (string) $itunes->duration,
                                        $matches
                                    )
                                ) {
                                    $seconds = ((int) $matches[1] * 3600) +
                                        ((int) $matches[2] * 60) +
                                        (int) $matches[3];
                                    $document->setMediaDuration($seconds);
                                }
                            }

                            $documents[] = $document;
                        }
                    }
                }
            }
        }

        return $documents;
    }

    abstract protected function injectMetaFromPodcastItem(
        ObjectManager $objectManager,
        DocumentInterface $document,
        \SimpleXMLElement $item,
    ): void;

    protected function getPodcastItemTitle(\SimpleXMLElement $item): ?string
    {
        return (string) $item->title.' – '.$this->getMediaTitle();
    }

    protected function getPodcastItemDescription(\SimpleXMLElement $item): ?string
    {
        return (string) $item->description;
    }

    protected function getPodcastItemCopyright(\SimpleXMLElement $item): ?string
    {
        $ituneNode = $item->children('itunes', true);
        $copyright = (string) $ituneNode->author;

        if (empty($copyright)) {
            $copyright = (string) $item->author;
        }
        if (empty($copyright)) {
            return $this->getMediaCopyright();
        }

        return $copyright.' – '.$this->getMediaCopyright();
    }

    #[\Override]
    public function getMediaFeed(?string $search = null): string
    {
        $url = $this->embedId;
        $response = $this->client->request('GET', $url);

        return $response->getContent();
    }

    #[\Override]
    public function getMediaTitle(): ?string
    {
        $feed = $this->getFeed();
        if ($feed instanceof \SimpleXMLElement && $feed->channel instanceof \SimpleXMLElement) {
            return (string) ($feed->channel->title ?? null);
        }

        return null;
    }

    #[\Override]
    public function getMediaDescription(): ?string
    {
        $feed = $this->getFeed();
        if ($feed instanceof \SimpleXMLElement && $feed->channel instanceof \SimpleXMLElement) {
            return (string) ($feed->channel->description ?? null);
        }

        return null;
    }

    #[\Override]
    public function getMediaCopyright(): ?string
    {
        $feed = $this->getFeed();
        if ($feed instanceof \SimpleXMLElement && $feed->channel instanceof \SimpleXMLElement) {
            return (string) ($feed->channel->copyright ?? null);
        }

        return null;
    }

    #[\Override]
    public function getThumbnailURL(): ?string
    {
        $feed = $this->getFeed();
        if (
            $feed instanceof \SimpleXMLElement
            && $feed->channel instanceof \SimpleXMLElement
            && $feed->channel->image instanceof \SimpleXMLElement
        ) {
            return (string) ($feed->channel->image->url ?? null);
        }

        return null;
    }

    #[\Override]
    protected function areDuplicatesAllowed(): bool
    {
        return true;
    }
}
