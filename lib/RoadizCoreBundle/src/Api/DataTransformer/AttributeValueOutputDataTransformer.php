<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Api\Dto\AttributeValueOutput;
use RZ\Roadiz\CoreBundle\Model\AttributeValueInterface;
use RZ\Roadiz\CoreBundle\Model\AttributeValueTranslationInterface;

/**
 * @deprecated Use AttributeValueNormalizer
 */
class AttributeValueOutputDataTransformer implements DataTransformerInterface
{
    /**
     * @inheritDoc
     */
    public function transform($data, string $to, array $context = []): object
    {
        if (!$data instanceof AttributeValueInterface) {
            throw new \InvalidArgumentException('Data to transform must be instance of ' . AttributeValueInterface::class);
        }
        $output = new AttributeValueOutput();
        $output->attribute = $data->getAttribute();
        $output->attributable = $data->getAttributable();
        $output->type = $data->getType();

        if (isset($context['translation']) && $context['translation'] instanceof TranslationInterface) {
            $translatedData = $data->getAttributeValueTranslation($context['translation']);
            $output->label = $data->getAttribute()->getLabelOrCode($context['translation']);
            if ($translatedData instanceof AttributeValueTranslationInterface) {
                $output->value = $translatedData->getValue();
            }
        }
        return $output;
    }

    /**
     * @inheritDoc
     */
    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        return AttributeValueOutput::class === $to && $data instanceof AttributeValueInterface;
    }
}
