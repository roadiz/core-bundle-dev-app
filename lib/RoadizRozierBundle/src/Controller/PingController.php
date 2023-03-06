<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Profiler\Profiler;

final class PingController extends AbstractController
{
    private ?Profiler $profiler;

    public function __construct(?Profiler $profiler)
    {
        $this->profiler = $profiler;
    }

    public function indexAction(): JsonResponse
    {
        // $profiler won't be set if your environment doesn't have the profiler (like prod, by default)
        if (null !== $this->profiler) {
            // if it exists, disable the profiler for this particular controller action
            $this->profiler->disable();
        }

        $this->denyAccessUnlessGranted('ROLE_BACKEND_USER');

        return new JsonResponse(null, Response::HTTP_ACCEPTED);
    }
}
