<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers\NodeTypes;

use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Themes\Rozier\RozierApp;

class NodeTypesController extends RozierApp
{
    public function __construct(
        private readonly NodeTypes $nodeTypesBag,
    ) {
    }

    public function indexAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_NODETYPES');

        $this->assignation['node_types'] = $this->nodeTypesBag->all();

        return $this->render('@RoadizRozier/node-types/list.html.twig', $this->assignation);
    }
}
