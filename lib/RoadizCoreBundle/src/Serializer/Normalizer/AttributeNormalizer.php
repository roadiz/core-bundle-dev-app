<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\Normalizer;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Attribute;
use RZ\Roadiz\CoreBundle\Entity\AttributeGroup;
use RZ\Roadiz\CoreBundle\Entity\AttributeTranslation;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Model\AttributeInterface;
use RZ\Roadiz\CoreBundle\Model\AttributeTranslationInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final readonly class AttributeNormalizer implements DenormalizerInterface
{
    public const PERSIST_NEW_ENTITIES = 'persist_new_entities';

    public function __construct(
        private ManagerRegistry $managerRegistry,
        private AttributeGroupNormalizer $attributeGroupNormalizer,
        private TranslationNormalizer $translationNormalizer,
    ) {
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Attribute::class => true,
        ];
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): Attribute
    {
        $code = $data['code'];
        $type = $data['type'];

        if (!is_string($code)) {
            throw new \InvalidArgumentException('Attribute code must be a string.');
        }

        $attribute = $this->managerRegistry->getRepository(Attribute::class)->findOneByCode($code);

        if (null === $attribute) {
            $attribute = new Attribute();
            $attribute->setCode($code);
            if ($context[self::PERSIST_NEW_ENTITIES] ?? false) {
                $this->managerRegistry->getManagerForClass(Attribute::class)->persist($attribute);
            }
        }

        $attribute->setType($type ?? AttributeInterface::STRING_T);
        $attribute->setColor($data['color'] ?? null);
        $attribute->setWeight($data['weight'] ?? null);
        $attribute->setSearchable($data['searchable'] ?? false);

        if (
            array_key_exists('group', $data)
            && is_array($data['group'])
            && array_key_exists('canonicalName', $data['group'])
        ) {
            $attributeGroup = $this->attributeGroupNormalizer->denormalize($data['group'], AttributeGroup::class, $format, $context);
            $attribute->setGroup($attributeGroup);
        }

        if (
            array_key_exists('attributeTranslations', $data)
            && is_array($data['attributeTranslations'])
        ) {
            foreach ($data['attributeTranslations'] as $translationData) {
                $this->denormalizeAttributeTranslation($translationData, $attribute, $format, $context);
            }
        }

        return $attribute;
    }

    private function denormalizeAttributeTranslation(
        mixed $data,
        Attribute $attribute,
        ?string $format = null,
        array $context = [],
    ): void {
        if (!is_array($data) || !is_string($data['label'] ?? null)) {
            // Ignore empty translations
            return;
        }
        if (!is_array($data['translation'] ?? null)) {
            throw new \InvalidArgumentException('Attribute group translation must be an array.');
        }
        if (!is_string($data['translation']['locale'] ?? null)) {
            throw new \InvalidArgumentException('Attribute group translation locale must be a string.');
        }
        $attributeTranslation = $attribute->getAttributeTranslations()->findFirst(
            fn ($key, AttributeTranslationInterface $translation) => $translation->getTranslation()->getLocale() === $data['translation']['locale']
        );
        if (null === $attributeTranslation) {
            $translation = $this->translationNormalizer->denormalize(
                $data['translation'],
                Translation::class,
                $format,
                $context
            );
            $attributeTranslation = new AttributeTranslation();
            $attributeTranslation->setTranslation($translation);
            $attribute->addAttributeTranslation($attributeTranslation);

            if ($context[AttributeNormalizer::PERSIST_NEW_ENTITIES] ?? false) {
                $this->managerRegistry->getManagerForClass(AttributeTranslation::class)->persist($attributeTranslation);
                $this->managerRegistry->getManagerForClass(AttributeTranslation::class)->flush();
            }
        }

        $attributeTranslation->setLabel($data['label']);
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null): bool
    {
        return is_array($data) && array_key_exists('type', $data) && array_key_exists('code', $data);
    }
}
