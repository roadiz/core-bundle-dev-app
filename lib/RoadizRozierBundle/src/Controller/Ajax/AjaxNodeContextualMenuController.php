<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Ajax;

use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    path: '/rz-admin/ajax/node/contextual-menu/{node}/{translation}',
    name: 'nodeContextualMenu',
    requirements: ['node' => '\d+', 'translation' => '\d+'],
    format: 'html',
)]
final class AjaxNodeContextualMenuController extends AbstractController
{
    public function __invoke(Node $node, Translation $translation): Response
    {
        // Only grant generic ROLE_ACCESS_NODES
        // Further access controls are done in the twig template
        $this->denyAccessUnlessGranted('ROLE_ACCESS_NODES');

        return $this->render(
            '@RoadizRozier/widgets/nodeTree/contextualMenu.html.twig',
            [
                'node' => $node,
                'translation' => $translation,
            ],
            new Response(status: Response::HTTP_PARTIAL_CONTENT),
        );
    }
}
