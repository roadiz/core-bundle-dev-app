<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\Normalizer;

use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Yaml\Yaml;

final readonly class NodeTypeFieldNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function __construct(
        #[Autowire(service: 'serializer.normalizer.object')]
        private NormalizerInterface $normalizer,
        #[Autowire(service: 'serializer.normalizer.object')]
        private DenormalizerInterface $denormalizer,
    ) {
    }

    /**
     * @return array|\ArrayObject|bool|float|int|string|null
     *
     * @throws ExceptionInterface
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): mixed
    {
        $data = $this->normalizer->normalize($object, $format, $context);

        /** @var NodeTypeField $object */
        if (is_array($data) && null !== $object->getDefaultValues()) {
            $data['defaultValues'] = Yaml::parse($object->getDefaultValues());
        }

        /** @var NodeTypeField $object */
        if (is_array($data) && null !== $object->getType()) {
            $data['type'] = preg_replace('#\.type$#', '', $object->getTypeName());
        }

        return $data;
    }

    public function supportsNormalization(mixed $data, ?string $format = null): bool
    {
        return $this->normalizer->supportsNormalization($data, $format);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            NodeTypeField::class => false,
        ];
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): NodeTypeField
    {
        $object = $this->denormalizer->denormalize($data, $type, $format, $context);

        if ($object instanceof NodeTypeField && is_string($data['type'])) {
            if (!str_ends_with('.type', $data['type'])) {
                $data['type'] = $data['type'].'.type';
            }
            if ($i = array_search($data['type'], NodeTypeField::$typeToHuman)) {
                $object->setType((int) $i);
            }
        }
        if (isset($data['defaultValues']) && is_array($data['defaultValues'])) {
            $object->setDefaultValues(Yaml::dump($data['defaultValues']));
        }

        return $object;
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null): bool
    {
        return $this->denormalizer->supportsDenormalization($data, $type, $format);
    }
}
