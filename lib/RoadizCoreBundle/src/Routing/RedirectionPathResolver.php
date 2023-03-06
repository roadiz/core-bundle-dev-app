<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Routing;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Redirection;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

final class RedirectionPathResolver implements PathResolverInterface
{
    private ManagerRegistry $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    public function resolvePath(
        string $path,
        array $supportedFormatExtensions = ['html'],
        bool $allowRootPaths = false,
        bool $allowNonReachableNodes = true
    ): ResourceInfo {
        $redirection = $this->managerRegistry
            ->getRepository(Redirection::class)
            ->findOneByQuery($path);

        if (null === $redirection) {
            throw new ResourceNotFoundException();
        }

        return (new ResourceInfo())
            ->setResource($redirection);
    }
}
