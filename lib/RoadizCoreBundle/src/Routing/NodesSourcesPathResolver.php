<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Routing;

use Doctrine\ORM\NonUniqueResultException;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use RZ\Roadiz\CoreBundle\Repository\NodesSourcesRepository;
use RZ\Roadiz\CoreBundle\Repository\TranslationRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Stopwatch\Stopwatch;

final readonly class NodesSourcesPathResolver implements PathResolverInterface
{
    public function __construct(
        private PreviewResolverInterface $previewResolver,
        private Stopwatch $stopwatch,
        private RequestStack $requestStack,
        private TranslationRepository $translationRepository,
        private NodesSourcesRepository $nodesSourcesRepository,
        private bool $useAcceptLanguageHeader,
        private bool $forceLocale,
    ) {
    }

    private function resolveHome(): ResourceInfo
    {
        $this->stopwatch->start('parseRootPath', 'routing');
        $translation = $this->parseTranslation();
        $nodeSource = $this->getHome($translation);
        $this->stopwatch->stop('parseRootPath');

        $resourceInfo = new ResourceInfo();
        $resourceInfo->setResource($nodeSource);
        $resourceInfo->setTranslation($nodeSource->getTranslation());
        $resourceInfo->setFormat('html');
        $resourceInfo->setLocale($nodeSource->getTranslation()->getPreferredLocale());

        return $resourceInfo;
    }

    #[\Override]
    public function resolvePath(
        string $path,
        array $supportedFormatExtensions = ['html'],
        bool $allowRootPaths = false,
        bool $allowNonReachableNodes = true,
    ): ResourceInfo {
        if (0 === count($supportedFormatExtensions)) {
            throw new \InvalidArgumentException('You must provide at least one supported format extension.');
        }
        $resourceInfo = new ResourceInfo();
        $tokens = $this->tokenizePath($path);

        if (0 === count($tokens) && !$allowRootPaths) {
            throw new ResourceNotFoundException();
        }

        if ('/' === $path) {
            return $this->resolveHome();
        }

        $identifier = '';
        if (count($tokens) > 0) {
            $identifier = strip_tags((string) $tokens[(int) (count($tokens) - 1)]);
        }

        if ('' === $identifier) {
            throw new ResourceNotFoundException();
        }
        /*
         * Look for any supported format extension after last token.
         */
        if (0 !== preg_match(
            '#^(?<slug>[a-zA-Z0-9\-\_\.]+)(?:\.(?<ext>'.implode('|', $supportedFormatExtensions).'))?$#',
            $identifier,
            $matches
        )) {
            $realIdentifier = $matches['slug'];
            $_format = $matches['ext'] ?? 'html';
            // replace last token with real node-name without extension.
            $tokens[(int) (count($tokens) - 1)] = $realIdentifier;
        } else {
            throw new ResourceNotFoundException();
        }

        $this->stopwatch->start('parseTranslation', 'routing');
        $translation = $this->parseTranslation($tokens);
        $this->stopwatch->stop('parseTranslation');
        /*
         * Try with URL Aliases OR nodeName
         */
        $this->stopwatch->start('parseFromIdentifier', 'routing');
        $nodeSource = $this->parseFromIdentifier($tokens, $translation, $allowNonReachableNodes);
        $this->stopwatch->stop('parseFromIdentifier');

        $resourceInfo->setResource($nodeSource);
        $resourceInfo->setTranslation($nodeSource->getTranslation());
        $resourceInfo->setFormat($_format);
        $resourceInfo->setLocale($nodeSource->getTranslation()->getPreferredLocale());

        return $resourceInfo;
    }

    /**
     * Split path into meaningful tokens.
     */
    private function tokenizePath(string $path): array
    {
        $tokens = explode('/', $path);

        return array_values(array_filter($tokens));
    }

    private function getHome(TranslationInterface $translation): NodesSources
    {
        $nodeSource = $this->nodesSourcesRepository->findOneBy([
            'node.home' => true,
            'translation' => $translation,
        ]);

        if (null === $nodeSource) {
            throw new ResourceNotFoundException('Home node source not found for translation: '.$translation->getLocale());
        }

        return $nodeSource;
    }

    /**
     * Parse translation from URL tokens even if it is not available yet.
     *
     * @param array<string> $tokens
     *
     * @throws NonUniqueResultException
     */
    private function parseTranslation(array &$tokens = []): ?TranslationInterface
    {
        $findOneByMethod = $this->previewResolver->isPreview() ?
            'findOneByLocaleOrOverrideLocale' :
            'findOneAvailableByLocaleOrOverrideLocale';

        if (!empty($tokens[0])) {
            $firstToken = $tokens[0];
            $locale = \mb_strtolower(strip_tags((string) $firstToken));
            // First token is for language and should not exceed 11 chars, i.e. tzm-Latn-DZ
            if (null !== $locale && '' != $locale && \mb_strlen($locale) <= 11) {
                $translation = $this->translationRepository->$findOneByMethod($locale);
                if (null !== $translation) {
                    return $translation;
                } elseif (in_array($tokens[0], Translation::getAvailableLocales())) {
                    throw new ResourceNotFoundException(sprintf('"%s" translation was not found.', $tokens[0]));
                }
            }
        }

        if (
            $this->useAcceptLanguageHeader
            && true === $this->forceLocale
        ) {
            /*
             * When no information to find locale is found and "forceLocale" is ON,
             * we must find translation based on Accept-Language header.
             * Be careful if you are using a reverse-proxy cache, YOU MUST VARY ON Accept-Language header.
             * @see https://varnish-cache.org/docs/6.3/users-guide/increasing-your-hitrate.html#http-vary
             */
            $request = $this->requestStack->getMainRequest();
            if (
                null !== $request
                && null !== $preferredLocale = $request->getPreferredLanguage($this->translationRepository->getAvailableLocales())
            ) {
                $translation = $this->translationRepository->$findOneByMethod($preferredLocale);
                if (null !== $translation) {
                    return $translation;
                }
            }
        }

        return $this->translationRepository->findDefault();
    }

    /**
     * @param array<string> $tokens
     */
    private function parseFromIdentifier(
        array &$tokens,
        ?TranslationInterface $translation = null,
        bool $allowNonReachableNodes = true,
    ): NodesSources {
        if (empty($tokens[0])) {
            return $this->getHome($translation);
        }

        if (1 === count($tokens) && in_array($tokens[0], Translation::getAvailableLocales())) {
            return $this->getHome($translation);
        }

        /*
         * If the only url token is not for language
         */
        $identifier = \mb_strtolower(strip_tags($tokens[(int) (count($tokens) - 1)]));
        if (empty($identifier)) {
            $this->stopwatch->stop('parseFromIdentifier');
            throw new ResourceNotFoundException();
        }

        $nodeSource = $this->nodesSourcesRepository->findOneByIdentifierAndTranslation(
            $identifier,
            $translation,
            !$this->previewResolver->isPreview()
        );

        if (null === $nodeSource) {
            $this->stopwatch->stop('parseFromIdentifier');
            throw new ResourceNotFoundException();
        }

        if (false === $allowNonReachableNodes && !$nodeSource->isReachable()) {
            $this->stopwatch->stop('parseFromIdentifier');
            throw new ResourceNotFoundException();
        }

        return $nodeSource;
    }
}
