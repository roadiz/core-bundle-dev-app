<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\Normalizer;

use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\CoreBundle\Entity\TagTranslation;
use RZ\Roadiz\Documents\Models\DocumentInterface;

/**
 * Override Tag default normalization.
 */
final class TagTranslationNormalizer extends AbstractPathNormalizer
{
    #[\Override]
    public function normalize(mixed $data, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $normalized = $this->decorated->normalize($data, $format, $context);
        if (
            $data instanceof Tag
            && is_array($normalized)
            && isset($context['translation'])
            && $context['translation'] instanceof TranslationInterface
        ) {
            $this->stopwatch->start('normalizeTag', 'serializer');
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
                    $normalized['documents'] = array_map(fn (DocumentInterface $document) => $this->decorated->normalize($document, $format, $documentsContext), $translatedData->getDocuments());
                }
            }
            $this->stopwatch->stop('normalizeTag');
        }

        return $normalized;
    }
}
