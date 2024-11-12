<?php

declare(strict_types=1);

namespace App\Controller;

use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class PageController extends AbstractController
{
    public function indexAction(NodesSources $nodeSource): Response
    {
        return $this->render('nodeSource/page.html.twig', [
            'nodeSource' => $nodeSource,
        ]);
    }
}
