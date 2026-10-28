<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\Enricher;

use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\Folder;
use RZ\Roadiz\CoreBundle\Entity\FolderTranslation;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

/**
 * Override Folder default normalization.
 */
#[AsTaggedItem(priority: 21)]
final readonly class FolderEnricher implements NormalizationEnricherInterface
{
    #[\Override]
    public function supports(object $data, ?string $format, array $context): bool
    {
        return $data instanceof Folder
            && isset($context['translation'])
            && $context['translation'] instanceof TranslationInterface;
    }

    /**
     * @param Folder $data
     */
    #[\Override]
    public function enrich(array $normalized, object $data, ?string $format, array $context): array
    {
        /*
         * Always falls back on default translation if no translation is found for Folders entities
         */
        $translatedData = $data->getTranslatedFoldersByTranslation($context['translation'])->first() ?:
            $data->getTranslatedFoldersByDefaultTranslation();
        if ($translatedData instanceof FolderTranslation) {
            $normalized['name'] = $translatedData->getName();
        }

        return $normalized;
    }
}
