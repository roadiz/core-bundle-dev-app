<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\DataTransformer;

use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use RZ\Roadiz\CoreBundle\Explorer\ExplorerProviderInterface;
use Symfony\Component\Form\DataTransformerInterface;

final readonly class ProviderDataTransformer implements DataTransformerInterface
{
    public function __construct(private NodeTypeField $nodeTypeField, private ExplorerProviderInterface $provider)
    {
    }

    #[\Override]
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

    #[\Override]
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
