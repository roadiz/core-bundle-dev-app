<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Node;

use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Exception\EntityAlreadyExistsException;
use RZ\Roadiz\CoreBundle\Node\NodeTranslator;
use RZ\Roadiz\RozierBundle\Form\TranslateNodeType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Themes\Rozier\RozierApp;

final class TranslateController extends RozierApp
{
    private NodeTranslator $nodeTranslator;

    /**
     * @param NodeTranslator $nodeTranslator
     */
    public function __construct(NodeTranslator $nodeTranslator)
    {
        $this->nodeTranslator = $nodeTranslator;
    }

    public function translateAction(Request $request, Node $nodeId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_NODES');

        $node = $nodeId;

        $availableTranslations = $this->em()
            ->getRepository(Translation::class)
            ->findUnavailableTranslationsForNode($node);

        if (count($availableTranslations) > 0) {
            $form = $this->createForm(TranslateNodeType::class, null, [
                'node' => $node,
            ]);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                /** @var Translation $destinationTranslation */
                $destinationTranslation = $form->get('translation')->getData();
                /** @var Translation $sourceTranslation */
                $sourceTranslation = $form->get('sourceTranslation')->getData();
                $translateOffspring = (bool) $form->get('translate_offspring')->getData();

                try {
                    $this->nodeTranslator->translateNode($sourceTranslation, $destinationTranslation, $node, $translateOffspring);
                    $this->em()->flush();
                    $msg = $this->getTranslator()->trans('node.%name%.translated', [
                        '%name%' => $node->getNodeName(),
                    ]);
                    /** @var NodesSources|false $nodeSource */
                    $nodeSource = $node->getNodeSources()->first();
                    $this->publishConfirmMessage(
                        $request,
                        $msg,
                        $nodeSource ?: null
                    );
                    return $this->redirectToRoute(
                        'nodesEditSourcePage',
                        ['nodeId' => $node->getId(), 'translationId' => $destinationTranslation->getId()]
                    );
                } catch (EntityAlreadyExistsException $e) {
                    $form->addError(new FormError($e->getMessage()));
                }
            }
            $this->assignation['form'] = $form->createView();
        }

        $this->assignation['node'] = $node;
        $this->assignation['translation'] = $this->em()->getRepository(Translation::class)->findDefault();
        $this->assignation['available_translations'] = [];

        foreach ($node->getNodeSources() as $ns) {
            $this->assignation['available_translations'][] = $ns->getTranslation();
        }

        return $this->render('@RoadizRozier/nodes/translate.html.twig', $this->assignation);
    }
}
