<?php

declare(strict_types=1);

namespace RZ\Roadiz\SolrBundle\Serializer\Normalizer;

use RZ\Roadiz\CoreBundle\SearchEngine\FacetedSearchResultsInterface;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[AsDecorator(decorates: 'api_platform.hydra.normalizer.collection')]
final readonly class FacetedCollectionNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    public function __construct(
        #[AutowireDecorated]
        private NormalizerInterface $decorated,
    ) {
    }

    #[\Override]
    public function normalize(mixed $data, ?string $format = null, array $context = []): \ArrayObject|array|string|int|float|bool|null
    {
        $normalized = $this->decorated->normalize($data, $format, $context);

        if ($data instanceof FacetedSearchResultsInterface && \is_array($normalized)) {
            $normalized['facets'] = $data->getFacets();
        }

        return $normalized;
    }

    #[\Override]
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $this->decorated->supportsNormalization($data, $format, $context);
    }

    #[\Override]
    public function getSupportedTypes(?string $format): array
    {
        return $this->decorated->getSupportedTypes($format);
    }

    #[\Override]
    public function setNormalizer(NormalizerInterface $normalizer): void
    {
        if ($this->decorated instanceof NormalizerAwareInterface) {
            $this->decorated->setNormalizer($normalizer);
        }
    }
}
