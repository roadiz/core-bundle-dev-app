<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\TwigExtension;

use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Preview\User\PreviewUserProviderInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class JwtExtension extends AbstractExtension
{
    public function __construct(
        private readonly JWTTokenManagerInterface $tokenManager,
        private readonly LoggerInterface $logger,
        private readonly PreviewUserProviderInterface $previewUserProvider
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('createPreviewJwt', [$this, 'createPreviewJwt']),
        ];
    }

    public function createPreviewJwt(): ?string
    {
        try {
            return $this->tokenManager->create($this->previewUserProvider->createFromSecurity());
        } catch (AccessDeniedException $exception) {
            $this->logger->warning($exception->getMessage());
            return null;
        } catch (JWTFailureException $exception) {
            $this->logger->warning($exception->getMessage());
            return null;
        }
    }
}
