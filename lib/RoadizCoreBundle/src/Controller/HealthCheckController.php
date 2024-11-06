<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class HealthCheckController
{
    public function __construct(
        private readonly ?string $healthCheckToken,
        private readonly ?string $appVersion,
        private readonly ?string $cmsVersion,
        private readonly ?string $cmsVersionPrefix,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        if (
            !empty($this->healthCheckToken)
            && $request->headers->get('x-health-check') !== $this->healthCheckToken
        ) {
            throw new NotFoundHttpException();
        }

        return new JsonResponse([
            'status' => 'pass',
            'version' => $this->appVersion ?? '',
            'notes' => [
                'roadiz_version' => $this->cmsVersion ?? '',
                'roadiz_channel' => $this->cmsVersionPrefix ?? '',
            ],
        ], Response::HTTP_OK, [
            'Content-type' => 'application/health+json',
            'Cache-Control' => 'public, max-age=10',
        ]);
    }
}
