<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\Normalizer;

use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\AttributeValue;
use RZ\Roadiz\CoreBundle\Model\AttributeValueTranslationInterface;
use RZ\Roadiz\Documents\Models\DocumentInterface;

/**
 * Override AttributeValue default normalization.
 */
final class AttributeValueNormalizer extends AbstractPathNormalizer
{
    #[\Override]
    public function normalize(mixed $data, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $normalized = $this->decorated->normalize($data, $format, $context);
        if ($data instanceof AttributeValue && is_array($normalized)) {
            $this->stopwatch->start('normalizeAttributeValue', 'serializer');
            /** @var array<string> $serializationGroups */
            $serializationGroups = isset($context['groups']) && is_array($context['groups']) ? $context['groups'] : [];

            $normalized['type'] = $data->getType();
            $normalized['code'] = $data->getAttribute()->getCode();
            $normalized['color'] = $data->getAttribute()->getColor();
            $normalized['weight'] = $data->getAttribute()->getWeight();

            if (isset($context['translation']) && $context['translation'] instanceof TranslationInterface) {
                $translatedData = $data->getAttributeValueTranslation($context['translation']);
                $normalized['label'] = $data->getAttribute()->getLabelOrCode($context['translation']);
                if (
                    $translatedData instanceof AttributeValueTranslationInterface
                    && null !== $translatedData->getValue()
                ) {
                    $normalized['value'] = $translatedData->getValue();
                } else {
                    $normalized['value'] = $data->getAttributeValueDefaultTranslation()?->getValue();
                }
            }

            if ($normalized['value'] instanceof \DateTimeInterface) {
                $normalized['value'] = $normalized['value']->format(\DateTimeInterface::ATOM);
            }

            if (\in_array('attribute_documents', $serializationGroups, true)) {
                $documentsContext = $context;
                $documentsContext['groups'] = ['document_display'];
                $normalized['documents'] = array_map(fn (DocumentInterface $document) => $this->decorated->normalize($document, $format, $documentsContext), $data->getAttribute()->getDocuments()->toArray());
            }
            $this->stopwatch->stop('normalizeAttributeValue');
        }

        return $normalized;
    }
}
