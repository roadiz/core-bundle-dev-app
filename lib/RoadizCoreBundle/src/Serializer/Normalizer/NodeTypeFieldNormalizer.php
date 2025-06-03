<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\Normalizer;

use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use RZ\Roadiz\CoreBundle\Enum\FieldType;
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
    #[\Override]
    public function normalize(mixed $object, ?string $format = null, array $context = []): mixed
    {
        $data = $this->normalizer->normalize($object, $format, $context);

        /** @var NodeTypeField $object */
        if (is_array($data) && null !== $object->getDefaultValues()) {
            $defaultValueParsed = Yaml::parse($object->getDefaultValues());
            if (is_string($defaultValueParsed)) {
                $defaultValueParsed = array_map('trim', explode(',', $defaultValueParsed));
            }
            $data['defaultValues'] = $defaultValueParsed;
        }

        /** @var NodeTypeField $object */
        if (is_array($data) && null !== $object->getType()) {
            $data['type'] = preg_replace('#\.type$#', '', $object->getTypeName());
        }

        return $data;
    }

    #[\Override]
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

    #[\Override]
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): NodeTypeField
    {
        $object = $this->denormalizer->denormalize($data, $type, $format, $context);

        if ($object instanceof NodeTypeField && is_string($data['type'])) {
            $object->setType(FieldType::fromHuman($data['type'])->value);
        }
        if (isset($data['defaultValues']) && is_array($data['defaultValues'])) {
            $object->setDefaultValues(Yaml::dump($data['defaultValues']));
        }

        return $object;
    }

    #[\Override]
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null): bool
    {
        return $this->denormalizer->supportsDenormalization($data, $type, $format);
    }
}
