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
    /**
     * @param mixed $object
     * @param string|null $format
     * @param array $context
     * @return array|\ArrayObject|bool|float|int|mixed|string|null
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $data = $this->decorated->normalize($object, $format, $context);
        if ($object instanceof AttributeValue && is_array($data)) {
            /** @var array<string> $serializationGroups */
            $serializationGroups = isset($context['groups']) && is_array($context['groups']) ? $context['groups'] : [];

            $data['type'] = $object->getType();
            $data['code'] = $object->getAttribute()->getCode();
            $data['color'] = $object->getAttribute()->getColor();
            $data['weight'] = $object->getAttribute()->getWeight();

            if (isset($context['translation']) && $context['translation'] instanceof TranslationInterface) {
                $translatedData = $object->getAttributeValueTranslation($context['translation']);
                $data['label'] = $object->getAttribute()->getLabelOrCode($context['translation']);
                if (
                    $translatedData instanceof AttributeValueTranslationInterface &&
                    $translatedData->getValue() !== null
                ) {
                    $data['value'] = $translatedData->getValue();
                } else {
                    $data['value'] = $object->getAttributeValueDefaultTranslation()?->getValue();
                }
            }

            if ($data['value'] instanceof \DateTimeInterface) {
                $data['value'] = $data['value']->format(\DateTimeInterface::ATOM);
            }

            if (\in_array('attribute_documents', $serializationGroups, true)) {
                $documentsContext = $context;
                $documentsContext['groups'] = ['document_display'];
                $data['documents'] = array_map(function (DocumentInterface $document) use ($format, $documentsContext) {
                    return $this->decorated->normalize($document, $format, $documentsContext);
                }, $object->getAttribute()->getDocuments()->toArray());
            }
        }
        return $data;
    }
}
