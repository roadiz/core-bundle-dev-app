<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Node;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Exception\EntityAlreadyExistsException;
use RZ\Roadiz\CoreBundle\Node\NodeOffspringResolverInterface;
use RZ\Roadiz\CoreBundle\Node\NodeTranslator;
use RZ\Roadiz\CoreBundle\Repository\AllStatusesNodesSourcesRepository;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeVoter;
use RZ\Roadiz\CoreBundle\Security\LogTrail;
use RZ\Roadiz\RozierBundle\Form\TranslateNodeType;
use RZ\Roadiz\RozierBundle\Message\TranslateNodeMessage;
use RZ\Roadiz\RozierBundle\TranslateAssistant\TranslateAssistantEstimator;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final class TranslateController extends AbstractController
{
    public function __construct(
        private readonly LogTrail $logTrail,
        private readonly ManagerRegistry $managerRegistry,
        private readonly TranslatorInterface $translator,
        private readonly NodeTranslator $nodeTranslator,
        private readonly AllStatusesNodesSourcesRepository $allStatusesNodesSourcesRepository,
        private readonly MessageBusInterface $bus,
        private readonly TranslateAssistantEstimator $translateAssistantEstimator,
        private readonly NodeOffspringResolverInterface $nodeOffspringResolver,
    ) {
    }

    #[Route(
        path: '/rz-admin/nodes/translate/{nodeId}',
        name: 'nodesTranslatePage',
        requirements: [
            'nodeId' => '[0-9]+',
        ],
        methods: ['GET', 'POST'],
    )]
    public function translateAction(
        Request $request,
        #[MapEntity(
            expr: 'repository.find(nodeId)',
            evictCache: true,
            message: 'Node does not exist'
        )]
        Node $node,
    ): Response {
        $this->denyAccessUnlessGranted(NodeVoter::EDIT_CONTENT, $node);

        /*
         * Subtree-wide, not node-only: once the node itself is translated, the language would
         * disappear from the form even though descendants still miss it — which used to leave
         * an interrupted subtree translation with no way to resume from the back office.
         */
        $subtreeNodeIds = $this->nodeOffspringResolver->getAllOffspringIds($node);
        $availableTranslations = $this->managerRegistry
            ->getRepository(Translation::class)
            ->findIncompleteTranslationsForNodes($subtreeNodeIds);
        $assignation = [];

        if (count($availableTranslations) > 0) {
            $form = $this->createForm(TranslateNodeType::class, null, [
                'node' => $node,
                'subtreeNodeIds' => $subtreeNodeIds,
            ]);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                /** @var Translation $destinationTranslation */
                $destinationTranslation = $form->get('translation')->getData();
                /** @var Translation $sourceTranslation */
                $sourceTranslation = $form->get('sourceTranslation')->getData();
                $translateOffspring = (bool) $form->get('translate_offspring')->getData();
                // Field is absent when no translate-assistant provider is configured.
                $useAssistant = $form->has('use_translate_assistant')
                    && (bool) $form->get('use_translate_assistant')->getData();

                // A dry run never translates anything, whatever the other checkboxes say.
                $isDryRun = $form->has('dry_run') && (bool) $form->get('dry_run')->getData();

                $estimate = null;
                if ($isDryRun || $useAssistant) {
                    $estimate = $this->translateAssistantEstimator->estimate(
                        $node,
                        $sourceTranslation,
                        $destinationTranslation,
                        $translateOffspring,
                    );
                    $assignation['estimate'] = $estimate;
                }

                if ($isDryRun) {
                    // Nothing to do: the estimate is the whole point, fall through to rendering.
                } elseif (!$useAssistant) {
                    try {
                        $this->nodeTranslator->translateNode($sourceTranslation, $destinationTranslation, $node, $translateOffspring);
                        $this->managerRegistry->getManagerForClass(NodesSources::class)?->flush();
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
                } elseif (null !== $estimate && !$estimate->fitsInQuota()) {
                    // Refuse up front rather than let the worker die halfway through the subtree.
                    $form->addError(new FormError(
                        $this->translator->trans('translate_assistant.estimate.exceeds_quota')
                    ));
                } else {
                    $this->bus->dispatch(new TranslateNodeMessage(
                        $node->getId(),
                        $sourceTranslation->getId(),
                        $destinationTranslation->getId(),
                        $translateOffspring,
                    ));
                    $this->logTrail->publishConfirmMessage(
                        $request,
                        $this->translator->trans('node.%name%.translation_in_progress', [
                            '%name%' => $node->getNodeName(),
                        ]),
                        $node->getNodeSources()->first() ?: null
                    );

                    return $this->redirectToRoute(
                        'nodesTranslateWaitingPage',
                        ['nodeId' => $node->getId(), 'translationId' => $destinationTranslation->getId()]
                    );
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

    #[Route(
        path: '/rz-admin/nodes/translate/{nodeId}/waiting/{translationId}',
        name: 'nodesTranslateWaitingPage',
        requirements: [
            'nodeId' => '[0-9]+',
            'translationId' => '[0-9]+',
        ],
        methods: ['GET'],
    )]
    public function waitingAction(
        Request $request,
        #[MapEntity(
            expr: 'repository.find(nodeId)',
            evictCache: true,
            message: 'Node does not exist'
        )]
        Node $node,
        #[MapEntity(
            expr: 'repository.find(translationId)',
            evictCache: true,
            message: 'Translation does not exist'
        )]
        Translation $translation,
    ): Response {
        $this->denyAccessUnlessGranted(NodeVoter::EDIT_CONTENT, $node);

        /*
         * The worker translates and flushes the cloned source in a single transaction:
         * once the source exists, its fields are already translated.
         */
        if (null !== $this->allStatusesNodesSourcesRepository->findOneByNodeAndTranslation($node, $translation)) {
            return $this->redirectToRoute(
                'nodesEditSourcePage',
                ['nodeId' => $node->getId(), 'translationId' => $translation->getId()]
            );
        }

        return $this->render('@RoadizRozier/nodes/translateWaiting.html.twig', [
            'node' => $node,
            'translation' => $translation,
            'try' => (int) $request->query->get('try', 0),
        ]);
    }
}
