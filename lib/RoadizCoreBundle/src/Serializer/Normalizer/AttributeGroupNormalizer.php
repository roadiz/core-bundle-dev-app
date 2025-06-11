<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\Normalizer;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\AttributeGroup;
use RZ\Roadiz\CoreBundle\Entity\AttributeGroupTranslation;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final readonly class AttributeGroupNormalizer implements DenormalizerInterface
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
        private TranslationNormalizer $translationNormalizer,
    ) {
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            AttributeGroup::class => true,
        ];
    }

    #[\Override]
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): AttributeGroup
    {
        $groupCanonicalName = $data['canonicalName'];
        if (!is_string($groupCanonicalName)) {
            throw new \InvalidArgumentException('Attribute group canonicalName must be a string.');
        }
        $attributeGroup = $this->managerRegistry
            ->getRepository(AttributeGroup::class)
            ->findOneByCanonicalName($groupCanonicalName);

        if (null === $attributeGroup) {
            $attributeGroup = new AttributeGroup();
            $attributeGroup->setCanonicalName($groupCanonicalName);
            if ($context[AttributeNormalizer::PERSIST_NEW_ENTITIES] ?? false) {
                $this->managerRegistry->getManagerForClass(AttributeGroup::class)->persist($attributeGroup);
                $this->managerRegistry->getManagerForClass(AttributeGroup::class)->flush();
            }
        }

        if (
            array_key_exists('attributeGroupTranslations', $data)
            && is_array($data['attributeGroupTranslations'])
        ) {
            foreach ($data['attributeGroupTranslations'] as $translationData) {
                $this->denormalizeAttributeGroupTranslation($translationData, $attributeGroup, $format, $context);
            }
        }

        return $attributeGroup;
    }

    private function denormalizeAttributeGroupTranslation(
        mixed $data,
        AttributeGroup $attributeGroup,
        ?string $format = null,
        array $context = [],
    ): void {
        if (!is_array($data) || !is_string($data['name'] ?? null)) {
            // Ignore empty translations
            return;
        }
        if (!is_array($data['translation'] ?? null)) {
            throw new \InvalidArgumentException('Attribute group translation must be an array.');
        }
        if (!is_string($data['translation']['locale'] ?? null)) {
            throw new \InvalidArgumentException('Attribute group translation locale must be a string.');
        }
        $attributeGroupTranslation = $attributeGroup->getAttributeGroupTranslations()->findFirst(
            fn ($key, AttributeGroupTranslation $translation) => $translation->getTranslation()->getLocale() === $data['translation']['locale']
        );
        if (null === $attributeGroupTranslation) {
            $translation = $this->translationNormalizer->denormalize(
                $data['translation'],
                Translation::class,
                $format,
                $context
            );
            $attributeGroupTranslation = new AttributeGroupTranslation();
            $attributeGroupTranslation->setTranslation($translation);
            $attributeGroup->addAttributeGroupTranslation($attributeGroupTranslation);

            if ($context[AttributeNormalizer::PERSIST_NEW_ENTITIES] ?? false) {
                $this->managerRegistry->getManagerForClass(AttributeGroupTranslation::class)->persist($attributeGroupTranslation);
                $this->managerRegistry->getManagerForClass(AttributeGroupTranslation::class)->flush();
            }
        }

        $attributeGroupTranslation->setName($data['name']);
    }

    #[\Override]
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null): bool
    {
        return is_array($data) && array_key_exists('canonicalName', $data);
    }
}
