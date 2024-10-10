<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\MediaFinders;

use Doctrine\Persistence\ObjectManager;
use GuzzleHttp\Client;
use League\Flysystem\FilesystemException;
use Psr\Http\Message\StreamInterface;
use RZ\Roadiz\Documents\AbstractDocumentFactory;
use RZ\Roadiz\Documents\DownloadedFile;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use RZ\Roadiz\Documents\Models\TimeableInterface;
use SimpleXMLElement;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractPodcastFinder extends AbstractEmbedFinder
{
    public static function supportEmbedUrl(string $embedUrl): bool
    {
        return false;
    }

    public static function getPlatform(): string
    {
        return 'podcast';
    }

    /**
     * @inheritDoc
     */
    protected function validateEmbedId(string $embedId = ""): string
    {
        return $embedId;
    }

    /**
     * @return array|SimpleXMLElement|null
     */
    public function getFeed()
    {
        if (null === $this->feed) {
            $rawFeed = $this->getMediaFeed();
            if ($rawFeed instanceof StreamInterface) {
                $rawFeed = $rawFeed->getContents();
            }
            if (null !== $rawFeed) {
                try {
                    $this->feed = new SimpleXMLElement($rawFeed);
                    return $this->feed;
                } catch (\Exception $errorException) {
                    throw new \RuntimeException('Feed content is not a valid Podcast XML');
                }
            }
        }
        return $this->feed;
    }

    /**
     * @param SimpleXMLElement $item
     *
     * @return string
     */
    protected function getAudioName(SimpleXMLElement $item): string
    {
        if (null !== $item->enclosure->attributes()) {
            $url = (string) $item->enclosure->attributes()->url;
        } else {
            throw new \RuntimeException('Podcast element does not have any enclosure URL.');
        }

        if (!empty((string) $item->title)) {
            $extension = pathinfo($url, PATHINFO_EXTENSION);
            return ((string) $item->title) . '.' . $extension;
        }
        return pathinfo($url, PATHINFO_BASENAME);
    }

    /**
     * Create a Document from an embed media.
     *
     * Be careful, this method does not flush.
     *
     * @param ObjectManager $objectManager
     * @param AbstractDocumentFactory $documentFactory
     * @return array<DocumentInterface>
     * @throws FilesystemException
     */
    public function createDocumentFromFeed(
        ObjectManager $objectManager,
        AbstractDocumentFactory $documentFactory
    ) {
        $documents = [];
        $feed = $this->getFeed();
        if ($feed instanceof SimpleXMLElement) {
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
        \SimpleXMLElement $item
    ): void;

    protected function getPodcastItemTitle(\SimpleXMLElement $item): ?string
    {
        return (string) $item->title . ' – ' . $this->getMediaTitle();
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
        return $copyright . ' – ' . $this->getMediaCopyright();
    }

    /**
     * @inheritDoc
     */
    public function getMediaFeed($search = null)
    {
        $url = $this->embedId;
        $client = new Client();
        $response = $client->get($url);

        if (Response::HTTP_OK == $response->getStatusCode()) {
            return $response->getBody();
        }

        throw new \RuntimeException($response->getReasonPhrase());
    }

    /**
     * @inheritDoc
     */
    public function getMediaTitle(): ?string
    {
        $feed = $this->getFeed();
        if ($feed instanceof SimpleXMLElement && $feed->channel instanceof SimpleXMLElement) {
            return (string) ($feed->channel->title ?? null);
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getMediaDescription(): ?string
    {
        $feed = $this->getFeed();
        if ($feed instanceof SimpleXMLElement && $feed->channel instanceof SimpleXMLElement) {
            return (string) ($feed->channel->description ?? null);
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getMediaCopyright(): ?string
    {
        $feed = $this->getFeed();
        if ($feed instanceof SimpleXMLElement && $feed->channel instanceof SimpleXMLElement) {
            return (string) ($feed->channel->copyright ?? null);
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getThumbnailURL(): ?string
    {
        $feed = $this->getFeed();
        if (
            $feed instanceof SimpleXMLElement
            && $feed->channel instanceof SimpleXMLElement
            && $feed->channel->image instanceof SimpleXMLElement
        ) {
            return (string) ($feed->channel->image->url ?? null);
        }
        return null;
    }

    protected function areDuplicatesAllowed(): bool
    {
        return true;
    }
}
