<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Cache;

use ApiPlatform\HttpCache\PurgerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Compacts cache tags before they are emitted or purged.
 *
 * The SAME transformation is applied on both sides (response header + purge
 * requests), so tagging and invalidation stay perfectly symmetric. xkey treats
 * tags as opaque tokens, so they don't need to remain human-readable.
 */
#[AsDecorator(
    decorates: 'api_platform.http_cache.purger',
    onInvalid: ContainerInterface::IGNORE_ON_INVALID_REFERENCE,
)]
final readonly class CompactTagPurger implements PurgerInterface
{
    public function __construct(
        #[AutowireDecorated]
        private PurgerInterface $decorated,
    ) {
    }

    public function purge(array $iris): void
    {
        $this->decorated->purge($this->compact($iris));
    }

    public function getResponseHeaders(array $iris): array
    {
        return $this->decorated->getResponseHeaders($this->compact($iris));
    }

    /** @param string[] $iris @return string[] */
    private function compact(array $iris): array
    {
        $tags = [];
        foreach ($iris as $iri) {
            $token = $this->tokenize($iri);
            $tags[$token] = $token; // preserve dedup
        }

        return array_values(array_filter($tags));
    }

    /**
     * Anchored, mutually-exclusive prefixes. The trailing slash makes them
     * unambiguous, so order is irrelevant (e.g. "nodes_sources/" can never be
     * matched by "nodes/").
     */
    private const array PREFIXES = [
        'documents/' => 'd/',
        'nodes/' => 'n/',
        'nodes_sources/' => 'ns/',
        'tags/' => 't/',
        'translations/' => 'tl/',
        'pages/' => 'p/',
        'custom_forms/' => 'cf/',
        'folders/' => 'f/',
    ];

    private function tokenize(string $iri): string
    {
        if (str_contains($iri, '.well-known/genid/')) {
            return '';
        }

        // Strip the "/api/" prefix and any leading slash — no regex needed.
        if (str_starts_with($iri, '/api/')) {
            $iri = substr($iri, 5);
        }
        $iri = ltrim($iri, '/');

        // First matching prefix wins; bail out of the loop immediately.
        foreach (self::PREFIXES as $from => $to) {
            if (str_starts_with($iri, $from)) {
                $iri = $to.substr($iri, \strlen($from));
                break;
            }
        }

        // The one non-anchored rule, applied after prefixes like the original.
        if (str_contains($iri, '_blocks/')) {
            $iri = str_replace('_blocks/', '_b/', $iri);
        }

        return $iri;
    }
}
