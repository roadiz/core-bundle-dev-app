<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Routing;

interface PathResolverInterface
{
    /**
     * Resolve a ResourceInfo containing AbstractEntity, format and translation using a unique path.
     *
     * @param array<string> $supportedFormatExtensions
     * @param bool          $allowRootPaths            Allow resolving / and /en, /fr paths to home pages
     * @param bool          $allowNonReachableNodes    Allow resolving non-reachable nodes
     */
    public function resolvePath(
        string $path,
        array $supportedFormatExtensions = ['html'],
        bool $allowRootPaths = false,
        bool $allowNonReachableNodes = true,
    ): ResourceInfo;
}
