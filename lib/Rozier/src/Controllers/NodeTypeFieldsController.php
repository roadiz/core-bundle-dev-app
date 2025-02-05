<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers;

use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Themes\Rozier\RozierApp;
use Twig\Error\RuntimeError;

class NodeTypeFieldsController extends RozierApp
{
    public function __construct(
        private readonly NodeTypes $nodeTypesBag,
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

        $this->assignation['nodeType'] = $nodeType;
        $this->assignation['fields'] = $fields;

        return $this->render('@RoadizRozier/node-type-fields/list.html.twig', $this->assignation);
    }
}
