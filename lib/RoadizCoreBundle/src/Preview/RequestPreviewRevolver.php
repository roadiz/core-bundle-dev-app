<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Preview;

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * BC Preview resolver to check Request-time then Kernel boot-time preview param.
 */
final readonly class RequestPreviewRevolver implements PreviewResolverInterface
{
    public function __construct(
        private RequestStack $requestStack,
        private string $requiredRole,
    ) {
    }

    #[\Override]
    public function isPreview(): bool
    {
        $request = $this->requestStack->getMainRequest();
        if (null === $request) {
            return false;
        }

        return $request->attributes->getBoolean('preview');
    }

    #[\Override]
    public function getRequiredRole(): string
    {
        return $this->requiredRole;
    }
}
