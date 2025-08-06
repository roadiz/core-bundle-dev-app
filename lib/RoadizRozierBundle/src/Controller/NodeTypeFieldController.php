<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller;

use RZ\Roadiz\CoreBundle\Bag\DecoratedNodeTypes;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Twig\Error\RuntimeError;

#[AsController]
final class NodeTypeFieldController extends AbstractController
{
    public function __construct(
        private readonly DecoratedNodeTypes $nodeTypesBag,
    ) {
    }

    /**
     * @throws RuntimeError
     */
    public function listAction(Request $request, string $nodeTypeName): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_NODETYPES');

        /** @var NodeType|null $nodeType */
        $nodeType = $this->nodeTypesBag->get($nodeTypeName);

        if (null === $nodeType) {
            throw new ResourceNotFoundException();
        }

        $fields = $nodeType->getFields();

        return $this->render('@RoadizRozier/node-type-fields/list.html.twig', [
            'nodeType' => $nodeType,
            'fields' => $fields,
        ]);
    }
}
