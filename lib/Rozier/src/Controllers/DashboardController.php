<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers;

use RZ\Roadiz\CoreBundle\Logger\Entity\Log;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Themes\Rozier\RozierApp;
use Twig\Error\RuntimeError;

class DashboardController extends RozierApp
{
    /**
     * @return Response $response
     *
     * @throws RuntimeError
     */
    public function indexAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_BACKEND_USER');

        $this->assignation['latestLogs'] = [];

        $this->assignation['latestLogs'] = $this->em()
             ->getRepository(Log::class)
             ->findLatestByNodesSources(8);

        return $this->render('@RoadizRozier/dashboard/index.html.twig', $this->assignation);
    }
}
