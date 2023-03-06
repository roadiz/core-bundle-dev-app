<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\MediaFinders;

use Doctrine\Persistence\ObjectManager;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use League\Flysystem\FilesystemException;
use Psr\Http\Message\StreamInterface;
use RZ\Roadiz\Documents\AbstractDocumentFactory;
use RZ\Roadiz\Documents\DownloadedFile;
use RZ\Roadiz\Documents\Exceptions\APINeedsAuthentificationException;
use RZ\Roadiz\Documents\Exceptions\EmbedDocumentAlreadyExistsException;
use RZ\Roadiz\Documents\Exceptions\InvalidEmbedId;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use RZ\Roadiz\Documents\Models\SizeableInterface;
use RZ\Roadiz\Documents\Models\TimeableInterface;
use RZ\Roadiz\Documents\OptionsResolver\ViewOptionsResolver;
use SimpleXMLElement;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Response;

/**
 * Abstract class to handle external media via their Json API.
 */
abstract class AbstractEmbedFinder implements EmbedFinderInterface
{
    /**
     * @var array|SimpleXMLElement|null
     */
    protected $feed = null;
    protected string $embedId;
    protected ?string $key = null;

    /**
     * @param  string $embedId
     * @param  bool   $validate Validate the embed id passed at the constructor [default: true].
     * @throws InvalidEmbedId When embedId string is malformed
     */
    public function __construct(string $embedId = '', bool $validate = true)
    {
        if ($validate) {
            $this->embedId = $this->validateEmbedId($embedId);
        } else {
            $this->embedId = $embedId;
        }
    }

    public function getShortType(): string
    {
        return $this->getPlatform();
    }

    public function isEmptyThumbnailAllowed(): bool
    {
        return false;
    }

    /**
     * @return string
     */
    public function getEmbedId(): string
    {
        return $this->embedId;
    }

    /**
     * @param string $embedId
     *
     * @return AbstractEmbedFinder
     */
    public function setEmbedId(string $embedId): AbstractEmbedFinder
    {
        $this->embedId = $this->validateEmbedId($embedId);
        return $this;
    }

    /**
     * Validate extern Id against platform naming policy.
     *
     * @param  string $embedId
     * @return string
     * @throws InvalidEmbedId When embedId string is malformed
     */
    protected function validateEmbedId(string $embedId = ""): string
    {
        if (preg_match('#(?<id>[^\/^=^?]+)$#', $embedId, $matches) === 1) {
            return $matches['id'];
        }
        throw new InvalidEmbedId($embedId);
    }

    /**
     * Tell if embed media exists after its API feed.
     *
     * @return bool
     */
    public function exists(): bool
    {
        return null !== $this->getFeed();
    }

    /**
     * Crawl and parse an API json feed for current embedID.
     *
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
                $feed = json_decode($rawFeed, true);
                if (is_array($feed)) {
                    $this->feed = $feed;
                } else {
                    $this->feed = null;
                }
            }
        }
        return $this->feed;
    }

    /**
     * Get embed media source URL.
     *
     * @param array $options
     *
     * @return string
     */
    public function getSource(array &$options = []): string
    {
        $resolver = new ViewOptionsResolver();
        $options = $resolver->resolve($options);

        return "";
    }

    /**
     * Crawl an embed API to get a Json feed.
     *
     * @param string|bool|null $search
     *
     * @return string|StreamInterface
     */
    abstract public function getMediaFeed($search = null);

    /**
     * Crawl an embed API to get a Json feed against a search query.
     *
     * @param string  $searchTerm
     * @param ?string $author
     * @param int     $maxResults
     *
     * @return string|StreamInterface|null
     */
    public function getSearchFeed(string $searchTerm, ?string $author = null, int $maxResults = 15)
    {
        return null;
    }

    /**
     * Compose an HTML iframe for viewing embed media.
     *
     * * width
     * * height
     * * title
     * * id
     * * class
     *
     * @param  array $options
     * @final
     * @return string
     */
    public function getIFrame(array &$options = []): string
    {
        $attributes = [];
        /*
         * getSource method will resolve all options for us.
         */
        $attributes['src'] = $this->getSource($options);
        $attributes['allow'] = [
            'accelerometer',
            'encrypted-media',
            'gyroscope',
            'picture-in-picture'
        ];

        if ($options['width'] > 0) {
            $attributes['width'] = $options['width'];

            /*
             * Default height is defined to 16:10
             */
            if ($options['height'] === 0) {
                $attributes['height'] = (int)(($options['width'] * 10) / 16);
            }
        }

        if ($options['height'] > 0) {
            $attributes['height'] = $options['height'];
        }

        $attributes['title'] = $options['title'];
        $attributes['id'] = $options['id'];
        $attributes['class'] = $options['class'];

        if ($options['autoplay']) {
            $attributes['allow'][] = 'autoplay';
        }

        if ($options['fullscreen']) {
            $attributes['allowFullScreen'] = true;
            $attributes['allow'][] = 'fullscreen';
        }

        $attributes['allow'] = implode('; ', $attributes['allow']);

        if ($options['loading']) {
            $attributes['loading'] = $options['loading'];
        }

        $attributes = array_filter($attributes);

        $htmlAttrs = [];
        foreach ($attributes as $key => $value) {
            if ($value == '' || $value === true) {
                $htmlAttrs[] = $key;
            } else {
                $htmlAttrs[] = $key . '="' . addslashes((string) $value) . '"';
            }
        }

        return '<iframe ' . implode(' ', $htmlAttrs) . '></iframe>';
    }

    /**
     * Create a Document from an embed media.
     *
     * Be careful, this method does not flush.
     *
     * @param ObjectManager $objectManager
     * @param AbstractDocumentFactory $documentFactory
     * @return DocumentInterface|array<DocumentInterface>
     * @throws FilesystemException
     */
    public function createDocumentFromFeed(
        ObjectManager $objectManager,
        AbstractDocumentFactory $documentFactory
    ) {
        if ($this->documentExists($objectManager, $this->getEmbedId(), $this->getPlatform())) {
            throw new EmbedDocumentAlreadyExistsException();
        }

        try {
            $file = $this->downloadThumbnail();

            if (!$this->exists() || (null === $file && !$this->isEmptyThumbnailAllowed())) {
                throw new \RuntimeException('no.embed.document.found');
            }

            if (null !== $file) {
                $documentFactory->setFile($file);
            }

            $document = $documentFactory->getDocument($this->isEmptyThumbnailAllowed(), $this->areDuplicatesAllowed());
            if (null !== $document) {
                /*
                 * Create document metas
                 * for each translation
                 */
                $this->injectMetaInDocument($objectManager, $document);
            }
        } catch (APINeedsAuthentificationException $exception) {
            $document = $documentFactory->getDocument(true, $this->areDuplicatesAllowed());
            $document?->setFilename($this->getPlatform() . '_' . $this->embedId . '.jpg');
        } catch (RequestException $exception) {
            $document = $documentFactory->getDocument(true, $this->areDuplicatesAllowed());
            $document?->setFilename($this->getPlatform() . '_' . $this->embedId . '.jpg');
        }

        if (null === $document) {
            throw new \RuntimeException('document.cannot_persist');
        }

        $document->setEmbedId($this->getEmbedId());
        $document->setEmbedPlatform($this->getPlatform());

        if ($document instanceof SizeableInterface) {
            $document->setImageWidth($this->getMediaWidth() ?? 0);
            $document->setImageHeight($this->getMediaHeight() ?? 0);
        }

        if ($document instanceof TimeableInterface) {
            $document->setMediaDuration($this->getMediaDuration() ?? 0);
        }

        return $document;
    }

    /**
     * @param  ObjectManager $objectManager
     * @param  string        $embedId
     * @param  string|null   $embedPlatform
     * @return bool
     */
    abstract protected function documentExists(
        ObjectManager $objectManager,
        string $embedId,
        ?string $embedPlatform
    ): bool;

    /**
     * Store additional information into Document.
     *
     * @param  ObjectManager     $objectManager
     * @param  DocumentInterface $document
     * @return DocumentInterface
     */
    abstract protected function injectMetaInDocument(ObjectManager $objectManager, DocumentInterface $document): DocumentInterface;

    /**
     * Get media title from feed.
     *
     * @return string|null
     */
    abstract public function getMediaTitle(): ?string;

    /**
     * Get media description from feed.
     *
     * @return string|null
     */
    abstract public function getMediaDescription(): ?string;

    /**
     * Get media copyright from feed.
     *
     * @return string|null
     */
    abstract public function getMediaCopyright(): ?string;

    /**
     * Get media thumbnail external URL from its feed.
     *
     * @return string|null
     */
    abstract public function getThumbnailURL(): ?string;

    /**
     * @return int|null
     */
    public function getMediaWidth(): ?int
    {
        return null;
    }

    /**
     * @return int|null
     */
    public function getMediaHeight(): ?int
    {
        return null;
    }

    /**
     * @return int|null
     */
    public function getMediaDuration(): ?int
    {
        return null;
    }

    /**
     * Send a CURL request and get its string output.
     *
     * @param string $url
     *
     * @return StreamInterface
     * @throws \RuntimeException
     */
    public function downloadFeedFromAPI(string $url): StreamInterface
    {
        $client = new Client();
        $response = $client->get($url);

        if (Response::HTTP_OK == $response->getStatusCode()) {
            return $response->getBody();
        }

        throw new \RuntimeException($response->getReasonPhrase());
    }

    /**
     * @param string $pathinfo
     *
     * @return string
     */
    public function getThumbnailName(string $pathinfo): string
    {
        return $this->getEmbedId() . '_' . $pathinfo;
    }

    /**
     * Download a picture from the embed media platform
     * to get a thumbnail.
     *
     * @return File|null
     */
    public function downloadThumbnail(): ?File
    {
        $url = $this->getThumbnailURL();

        if (null !== $url && '' !== $url) {
            $thumbnailName = $this->getThumbnailName(basename($url));
            return DownloadedFile::fromUrl($url, $thumbnailName);
        }

        return null;
    }

    /**
     * Gets the value of key.
     *
     * Key is the access_token which could be asked to consume an API.
     * For example, for Youtube it must be your API server key. For SoundCloud
     * it should be you app client Id.
     *
     * @return string|null
     */
    public function getKey(): ?string
    {
        return $this->key;
    }

    /**
     * Sets the value of key.
     *
     * Key is the access_token which could be asked to consume an API.
     * For example, for Youtube it must be your API server key. For Soundcloud
     * it should be you app client Id.
     *
     * @param string|null $key the key
     *
     * @return self
     */
    public function setKey(?string $key)
    {
        $this->key = $key;
        return $this;
    }

    protected function areDuplicatesAllowed(): bool
    {
        return false;
    }
}
