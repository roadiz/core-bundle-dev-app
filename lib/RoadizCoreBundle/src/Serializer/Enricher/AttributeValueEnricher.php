<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\Enricher;

use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\AttributeValue;
use RZ\Roadiz\CoreBundle\Model\AttributeValueTranslationInterface;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

/**
 * Override AttributeValue default normalization.
 */
#[AsTaggedItem(priority: 15)]
final class AttributeValueEnricher implements NormalizationEnricherInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    #[\Override]
    public function supports(object $data, ?string $format, array $context): bool
    {
        return $data instanceof AttributeValue;
    }

    /**
     * @param AttributeValue $data
     */
    #[\Override]
    public function enrich(array $normalized, object $data, ?string $format, array $context): array
    {
        /** @var array<string> $serializationGroups */
        $serializationGroups = isset($context['groups']) && is_array($context['groups']) ? $context['groups'] : [];

        $normalized['type'] = $data->getType();
        $normalized['code'] = $data->getAttribute()?->getCode();
        $normalized['color'] = $data->getAttribute()?->getColor();
        $normalized['weight'] = $data->getAttribute()?->getWeight();

        if (isset($context['translation']) && $context['translation'] instanceof TranslationInterface) {
            $translatedData = $data->getAttributeValueTranslation($context['translation']);
            $normalized['label'] = $data->getAttribute()?->getLabelOrCode($context['translation']);
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
            $normalized['documents'] = array_map(
                fn (DocumentInterface $document) => $this->normalizer->normalize($document, $format, $documentsContext),
                $data->getAttribute()?->getDocuments()->toArray() ?? []
            );
        }

        return $normalized;
    }
}
