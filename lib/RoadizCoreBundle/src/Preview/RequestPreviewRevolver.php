<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Preview;

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * BC Preview resolver to check Request-time then Kernel boot-time preview param.
 *
 * @package RZ\Roadiz\CoreBundle\Preview
 */
final class RequestPreviewRevolver implements PreviewResolverInterface
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly string $requiredRole
    ) {
    }

    /**
     * @return bool
     */
    public function isPreview(): bool
    {
        $request = $this->requestStack->getMainRequest();
        if (null === $request) {
            return false;
        }
        return $request->attributes->getBoolean('preview');
    }

    public function getRequiredRole(): string
    {
        return $this->requiredRole;
    }
}
