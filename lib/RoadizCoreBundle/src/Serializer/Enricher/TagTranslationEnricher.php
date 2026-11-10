<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\Enricher;

use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\CoreBundle\Entity\TagTranslation;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

/**
 * Override Tag default normalization.
 */
#[AsTaggedItem(priority: 18)]
final class TagTranslationEnricher implements NormalizationEnricherInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    #[\Override]
    public function supports(object $data, ?string $format, array $context): bool
    {
        return $data instanceof Tag
            && isset($context['translation'])
            && $context['translation'] instanceof TranslationInterface;
    }

    /**
     * @param Tag $data
     */
    #[\Override]
    public function enrich(array $normalized, object $data, ?string $format, array $context): array
    {
        /** @var array<string> $serializationGroups */
        $serializationGroups = isset($context['groups']) && is_array($context['groups']) ? $context['groups'] : [];
        /*
         * Always falls back on default translation if no translation is found for Tags entities
         */
        $translatedData = $data->getTranslatedTagsByTranslation($context['translation'])->first() ?:
            $data->getTranslatedTagsByDefaultTranslation();
        if ($translatedData instanceof TagTranslation) {
            $normalized['name'] = $translatedData->getName();
            $normalized['description'] = $translatedData->getDescription();

            if (\in_array('tag_documents', $serializationGroups, true)) {
                $documentsContext = $context;
                $documentsContext['groups'] = ['document_display'];
                $normalized['documents'] = array_map(fn (DocumentInterface $document) => $this->normalizer->normalize($document, $format, $documentsContext), $translatedData->getDocuments());
            }
        }

        return $normalized;
    }
}
