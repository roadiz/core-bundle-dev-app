<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Node;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Exception\EntityAlreadyExistsException;
use RZ\Roadiz\CoreBundle\Node\NodeTranslator;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeVoter;
use RZ\Roadiz\CoreBundle\Security\LogTrail;
use RZ\Roadiz\RozierBundle\Form\TranslateNodeType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

final class TranslateController extends AbstractController
{
    public function __construct(
        private readonly LogTrail $logTrail,
        private readonly ManagerRegistry $managerRegistry,
        private readonly TranslatorInterface $translator,
        private readonly NodeTranslator $nodeTranslator,
    ) {
    }

    public function translateAction(Request $request, Node $nodeId): Response
    {
        $this->denyAccessUnlessGranted(NodeVoter::EDIT_CONTENT, $nodeId);

        $node = $nodeId;
        $availableTranslations = $this->managerRegistry
            ->getRepository(Translation::class)
            ->findUnavailableTranslationsForNode($node);
        $assignation = [];

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
                    $this->managerRegistry->getManagerForClass(NodesSources::class)->flush();
                    $msg = $this->translator->trans('node.%name%.translated', [
                        '%name%' => $node->getNodeName(),
                    ]);
                    /** @var NodesSources|false $nodeSource */
                    $nodeSource = $node->getNodeSources()->first();
                    $this->logTrail->publishConfirmMessage(
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
            $assignation['form'] = $form->createView();
        }

        $assignation['node'] = $node;
        $assignation['translation'] = $this->managerRegistry->getRepository(Translation::class)->findDefault();
        $assignation['available_translations'] = [];

        foreach ($node->getNodeSources() as $ns) {
            $assignation['available_translations'][] = $ns->getTranslation();
        }

        return $this->render('@RoadizRozier/nodes/translate.html.twig', $assignation);
    }
}
