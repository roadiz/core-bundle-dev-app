<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Api\Dto\AttributeOutput;
use RZ\Roadiz\CoreBundle\Model\AttributeInterface;

/**
 * @deprecated
 */
class AttributeOutputDataTransformer implements DataTransformerInterface
{
    /**
     * @inheritDoc
     */
    public function transform($data, string $to, array $context = []): object
    {
        if (!$data instanceof AttributeInterface) {
            throw new \InvalidArgumentException('Data to transform must be instance of ' . AttributeInterface::class);
        }
        $output = new AttributeOutput();
        $output->group = $data->getGroup();
        $output->code = $data->getCode();
        $output->type = $data->getType();
        $output->color = $data->getColor();
        $output->documents = $data->getDocuments()->toArray();

        if (isset($context['translation']) && $context['translation'] instanceof TranslationInterface) {
            $output->name = $data->getLabelOrCode($context['translation']);
        }
        return $output;
    }

    /**
     * @inheritDoc
     */
    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        return AttributeOutput::class === $to && $data instanceof AttributeInterface;
    }
}
