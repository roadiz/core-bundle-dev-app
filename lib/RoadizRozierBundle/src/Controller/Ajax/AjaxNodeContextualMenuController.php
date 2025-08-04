<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Ajax;

use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class AjaxNodeContextualMenuController extends AbstractController
{
    public function __invoke(Node $node, Translation $translation): Response
    {
        $this->denyAccessUnlessGranted(NodeVoter::EDIT_SETTING, $node);

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
