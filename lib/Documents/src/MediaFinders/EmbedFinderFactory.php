<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\MediaFinders;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class EmbedFinderFactory
{
    /**
     * @param array<string, class-string<EmbedFinderInterface>> $embedPlatforms
     */
    public function __construct(
        protected readonly HttpClientInterface $client,
        /**
         * Embed platform classes, for example:
         *
         * [
         *    youtube => YoutubeEmbedFinder::class,
         *    vimeo => VimeoEmbedFinder::class
         * ]
         */
        private array $embedPlatforms = [],
    ) {
    }

    public function createForPlatform(?string $mediaPlatform, ?string $embedId): ?EmbedFinderInterface
    {
        if (null !== $embedId && $this->supports($mediaPlatform)) {
            /**
             * @var class-string<EmbedFinderInterface> $class
             */
            $class = $this->embedPlatforms[$mediaPlatform];

            return new $class($this->client, $embedId);
        }

        return null;
    }

    public function createForUrl(?string $embedUrl): ?EmbedFinderInterface
    {
        if (null === $embedUrl) {
            throw new \InvalidArgumentException('"embedUrl" is required');
        }
        // Throws a BadRequestHttpException if the embedUrl is not a string
        if (!is_string($embedUrl)) {
            throw new \InvalidArgumentException('"embedUrl" must be a string');
        }
        // Throws a BadRequestHttpException if the embedUrl is not a valid URL
        if (!filter_var($embedUrl, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('"embedUrl" is not a valid URL');
        }

        /**
         * @var string                             $platform
         * @var class-string<EmbedFinderInterface> $class
         */
        foreach ($this->embedPlatforms as $platform => $class) {
            $callback = [$class, 'supportEmbedUrl'];
            if (
                is_callable($callback)
                && call_user_func($callback, $embedUrl)
            ) {
                return $this->createForPlatform($platform, $embedUrl);
            }
        }

        return null;
    }

    public function supports(?string $mediaPlatform): bool
    {
        return
            null !== $mediaPlatform
            && in_array(
                $mediaPlatform,
                array_keys($this->embedPlatforms)
            );
    }
}
