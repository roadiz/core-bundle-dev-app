<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\NodeType;

use RZ\Roadiz\CoreBundle\Bag\DecoratedNodeTypes;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;

#[AsController]
final class NodeTypeController extends AbstractController
{
    public function __construct(
        private readonly DecoratedNodeTypes $nodeTypesBag,
    ) {
    }

    public function indexAction(
        #[MapQueryParameter(filter: \FILTER_VALIDATE_REGEXP, options: ['regexp' => '/ASC|DESC/'])]
        ?string $ordering = 'ASC',
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_NODETYPES');

        return $this->render('@RoadizRozier/node-types/list.html.twig', [
            'node_types' => $this->nodeTypesBag->allSorted($ordering),
        ]);
    }
}
