<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\TranslateAssistant;

use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Node\NodeOffspringResolverInterface;
use RZ\Roadiz\CoreBundle\Repository\AllStatusesNodesSourcesRepository;

/**
 * Prices a subtree translation before it is dispatched, so an editor never starts a run the
 * quota cannot pay for.
 *
 * The character count comes from NodesSourcesTranslator itself, so what is priced is exactly
 * what would be sent.
 */
final readonly class TranslateAssistantEstimator
{
    public function __construct(
        private NodesSourcesTranslator $translator,
        private NodeOffspringResolverInterface $nodeOffspringResolver,
        private AllStatusesNodesSourcesRepository $allStatusesNodesSourcesRepository,
        private NodeTypes $nodeTypesBag,
        private TranslateAssistantInterface $translateAssistant,
    ) {
    }

    public function estimate(
        Node $node,
        TranslationInterface $sourceTranslation,
        TranslationInterface $destinationTranslation,
        bool $withChildren,
    ): TranslateAssistantEstimate {
        $nodeIds = $withChildren
            ? $this->nodeOffspringResolver->getAllOffspringIds($node)
            : array_filter([$node->getId()]);

        // Nodes already translated are skipped by the handler, so they cost nothing. Resolved
        // once, not per source.
        $alreadyTranslated = array_map(
            fn (NodesSources $source) => $source->getNode()->getId(),
            $this->sourcesFor($nodeIds, $destinationTranslation)
        );

        $characters = 0;
        $sourceCount = 0;

        // ponytail: hydrates every source of the subtree. Switch to a partial select if a
        // very deep tree ever makes this heavy.
        foreach ($this->sourcesFor($nodeIds, $sourceTranslation) as $source) {
            if (in_array($source->getNode()->getId(), $alreadyTranslated, true)) {
                continue;
            }
            $fields = $this->nodeTypesBag->get($source->getNodeTypeName())?->getFields() ?? [];
            $sourceCharacters = $this->translator->countTranslatableCharacters($source, $fields);
            if (0 === $sourceCharacters) {
                continue;
            }
            $characters += $sourceCharacters;
            ++$sourceCount;
        }

        $usage = $this->translateAssistant->usage();

        return new TranslateAssistantEstimate(
            characterCount: $characters,
            sourceCount: $sourceCount,
            // Snapshot: usage() is cached, and per-field AI buttons can spend in the meantime.
            charactersRemaining: null !== $usage ? max(0, $usage->characterLimit - $usage->characterCount) : null,
        );
    }

    /**
     * @param array<int> $nodeIds
     *
     * @return array<NodesSources>
     */
    private function sourcesFor(array $nodeIds, TranslationInterface $translation): array
    {
        if ([] === $nodeIds) {
            return [];
        }

        return $this->allStatusesNodesSourcesRepository->findBy([
            'node' => $nodeIds,
            'translation' => $translation,
        ]);
    }
}
