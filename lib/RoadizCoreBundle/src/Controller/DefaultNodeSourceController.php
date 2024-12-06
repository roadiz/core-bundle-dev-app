<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Controller;

use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class DefaultNodeSourceController extends AbstractController
{
    public function indexAction(NodesSources $nodeSource): Response
    {
        return $this->render('@RoadizCore/nodeSource/default.html.twig', [
            'nodeSource' => $nodeSource,
        ]);
    }
}
