<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Routing;

use Symfony\Component\Routing\Exception\ResourceNotFoundException;

final class ChainResourcePathResolver implements PathResolverInterface
{
    /**
     * @var array<PathResolverInterface>
     */
    private array $pathResolvers = [];

    public function addPathResolver(PathResolverInterface $pathResolver): ChainResourcePathResolver
    {
        $this->pathResolvers[$pathResolver::class] = $pathResolver;

        return $this;
    }

    #[\Override]
    public function resolvePath(
        string $path,
        array $supportedFormatExtensions = ['html'],
        bool $allowRootPaths = false,
        bool $allowNonReachableNodes = true,
    ): ResourceInfo {
        if (0 === count($this->pathResolvers)) {
            throw new ResourceNotFoundException('No PathResolverInterface was registered to resolve path');
        }
        foreach ($this->pathResolvers as $pathResolver) {
            try {
                return $pathResolver->resolvePath($path, $supportedFormatExtensions, $allowRootPaths, $allowNonReachableNodes);
            } catch (ResourceNotFoundException) {
                // Do nothing to allow other resolver to work.
            }
        }
        // If none responds, throws ResourceNotFoundException
        throw new ResourceNotFoundException('None of the chained PathResolverInterface were able to resolve path');
    }
}
