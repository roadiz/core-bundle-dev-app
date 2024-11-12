<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\DataTransformer;

use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use RZ\Roadiz\CoreBundle\Explorer\ExplorerProviderInterface;
use Symfony\Component\Form\DataTransformerInterface;

class ProviderDataTransformer implements DataTransformerInterface
{
    protected NodeTypeField $nodeTypeField;
    protected ExplorerProviderInterface $provider;

    public function __construct(NodeTypeField $nodeTypeField, ExplorerProviderInterface $provider)
    {
        $this->nodeTypeField = $nodeTypeField;
        $this->provider = $provider;
    }

    public function transform(mixed $value): ?array
    {
        if (null === $value) {
            return null;
        }

        if (!is_array($value)) {
            $value = [$value];
        }

        $value = array_filter($value);

        if (0 === count($value)) {
            return null;
        }

        return $this->provider->getItemsById($value);
    }

    public function reverseTransform(mixed $value): mixed
    {
        if (
            is_array($value)
            && $this->nodeTypeField->isSingleProvider()
            && isset($value[0])
        ) {
            return $value[0];
        }

        return $value;
    }
}
