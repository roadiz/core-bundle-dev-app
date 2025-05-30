<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Component\Routing\Attribute\Route;

class PingController extends AbstractController
{
    public function __construct(private readonly ?Profiler $profiler)
    {
    }

    #[Route('/rz-admin/ping', name: 'ping', methods: ['GET'])]
    public function pingAction(): JsonResponse
    {
        // $profiler won't be set if your environment doesn't have the profiler (like prod, by default)
        $this->profiler?->disable();

        $this->denyAccessUnlessGranted('ROLE_BACKEND_USER');

        return new JsonResponse(null, Response::HTTP_ACCEPTED);
    }
}
