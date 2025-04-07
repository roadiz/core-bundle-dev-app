<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers\NodeTypes;

use RZ\Roadiz\CoreBundle\Bag\DecoratedNodeTypes;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
final class NodeTypesController extends AbstractController
{
    public function __construct(
        private readonly DecoratedNodeTypes $nodeTypesBag,
    ) {
    }

    public function indexAction(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_NODETYPES');

        return $this->render('@RoadizRozier/node-types/list.html.twig', [
            'node_types' => $this->nodeTypesBag->all(),
        ]);
    }
}
