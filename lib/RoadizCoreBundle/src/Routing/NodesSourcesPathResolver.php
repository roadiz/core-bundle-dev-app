<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Routing;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Bag\Settings;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use RZ\Roadiz\CoreBundle\Repository\TranslationRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Stopwatch\Stopwatch;

final class NodesSourcesPathResolver implements PathResolverInterface
{
    private static string $nodeNamePattern = '[a-zA-Z0-9\-\_\.]+';

    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly PreviewResolverInterface $previewResolver,
        private readonly Stopwatch $stopwatch,
        private readonly Settings $settingsBag,
        private readonly RequestStack $requestStack,
        private readonly bool $useAcceptLanguageHeader,
    ) {
    }

    public function resolvePath(
        string $path,
        array $supportedFormatExtensions = ['html'],
        bool $allowRootPaths = false,
        bool $allowNonReachableNodes = true,
    ): ResourceInfo {
        $resourceInfo = new ResourceInfo();
        $tokens = $this->tokenizePath($path);
        $_format = 'html';

        if (0 === count($tokens) && !$allowRootPaths) {
            throw new ResourceNotFoundException();
        }

        if ('/' === $path) {
            $this->stopwatch->start('parseRootPath', 'routing');
            $translation = $this->parseTranslation();
            $nodeSource = $this->getHome($translation);
            $this->stopwatch->stop('parseRootPath');
        } else {
            $identifier = '';
            if (count($tokens) > 0) {
                $identifier = strip_tags($tokens[(int) (count($tokens) - 1)]);
            }

            if ('' !== $identifier) {
                /*
                 * Prevent searching nodes with special characters.
                 */
                if (0 === preg_match('#'.static::$nodeNamePattern.'#', $identifier)) {
                    throw new ResourceNotFoundException();
                }

                /*
                 * Look for any supported format extension after last token.
                 */
                if (
                    0 !== preg_match(
                        '#^('.static::$nodeNamePattern.')\.('.implode('|', $supportedFormatExtensions).')$#',
                        $identifier,
                        $matches
                    )
                ) {
                    $realIdentifier = $matches[1];
                    $_format = $matches[2];
                    // replace last token with real node-name without extension.
                    $tokens[(int) (count($tokens) - 1)] = $realIdentifier;
                }
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
        }

        if (null === $nodeSource) {
            throw new ResourceNotFoundException();
        }

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
        $tokens = array_values(array_filter($tokens));

        return $tokens;
    }

    private function getHome(TranslationInterface $translation): ?NodesSources
    {
        /*
         * Resolve home page
         * @phpstan-ignore-next-line
         */
        return $this->managerRegistry
            ->getRepository(NodesSources::class)
            ->findOneBy([
                'node.home' => true,
                'translation' => $translation,
            ]);
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
        /** @var TranslationRepository $repository */
        $repository = $this->managerRegistry->getRepository(Translation::class);
        $findOneByMethod = $this->previewResolver->isPreview() ?
            'findOneByLocaleOrOverrideLocale' :
            'findOneAvailableByLocaleOrOverrideLocale';

        if (!empty($tokens[0])) {
            $firstToken = $tokens[0];
            $locale = \mb_strtolower(strip_tags((string) $firstToken));
            // First token is for language and should not exceed 11 chars, i.e. tzm-Latn-DZ
            if (null !== $locale && '' != $locale && \mb_strlen($locale) <= 11) {
                $translation = $repository->$findOneByMethod($locale);
                if (null !== $translation) {
                    return $translation;
                } elseif (in_array($tokens[0], Translation::getAvailableLocales())) {
                    throw new ResourceNotFoundException(sprintf('"%s" translation was not found.', $tokens[0]));
                }
            }
        }

        if (
            $this->useAcceptLanguageHeader
            && true === $this->settingsBag->get('force_locale', false)
        ) {
            /*
             * When no information to find locale is found and "force_locale" is ON,
             * we must find translation based on Accept-Language header.
             * Be careful if you are using a reverse-proxy cache, YOU MUST VARY ON Accept-Language header.
             * @see https://varnish-cache.org/docs/6.3/users-guide/increasing-your-hitrate.html#http-vary
             */
            $request = $this->requestStack->getMainRequest();
            if (
                null !== $request
                && null !== $preferredLocale = $request->getPreferredLanguage($repository->getAvailableLocales())
            ) {
                $translation = $repository->$findOneByMethod($preferredLocale);
                if (null !== $translation) {
                    return $translation;
                }
            }
        }

        return $repository->findDefault();
    }

    /**
     * @param array<string> $tokens
     */
    private function parseFromIdentifier(
        array &$tokens,
        ?TranslationInterface $translation = null,
        bool $allowNonReachableNodes = true,
    ): ?NodesSources {
        if (!empty($tokens[0])) {
            /*
             * If the only url token is not for language
             */
            if (count($tokens) > 1 || !in_array($tokens[0], Translation::getAvailableLocales())) {
                $identifier = \mb_strtolower(strip_tags($tokens[(int) (count($tokens) - 1)]));
                if (null !== $identifier && '' != $identifier) {
                    $array = $this->managerRegistry
                        ->getRepository(Node::class)
                        ->findNodeTypeNameAndSourceIdByIdentifier(
                            $identifier,
                            $translation,
                            !$this->previewResolver->isPreview(),
                            $allowNonReachableNodes
                        );
                    if (null !== $array) {
                        /** @var NodesSources|null $nodeSource */
                        $nodeSource = $this->managerRegistry
                            ->getRepository($this->getNodeTypeClassname($array['name']))
                            ->findOneBy([
                                'id' => $array['id'],
                            ]);

                        return $nodeSource;
                    } else {
                        $this->stopwatch->stop('parseFromIdentifier');
                        throw new ResourceNotFoundException(sprintf('"%s" was not found.', $identifier));
                    }
                } else {
                    $this->stopwatch->stop('parseFromIdentifier');
                    throw new ResourceNotFoundException();
                }
            }
        }

        return $this->getHome($translation);
    }

    /**
     * @return class-string
     */
    private function getNodeTypeClassname(string $name): string
    {
        $fqcn = NodeType::getGeneratedEntitiesNamespace().'\\NS'.ucwords($name);
        if (!class_exists($fqcn)) {
            throw new ResourceNotFoundException($fqcn.' entity does not exist.');
        }

        return $fqcn;
    }
}
