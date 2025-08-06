<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\Normalizer;

use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\Folder;
use RZ\Roadiz\CoreBundle\Entity\FolderTranslation;

/**
 * Override Folder default normalization.
 */
final class FolderNormalizer extends AbstractPathNormalizer
{
    #[\Override]
    public function normalize(mixed $data, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $normalized = $this->decorated->normalize($data, $format, $context);
        if ($data instanceof Folder && is_array($normalized)) {
            if (isset($context['translation']) && $context['translation'] instanceof TranslationInterface) {
                /*
                 * Always falls back on default translation if no translation is found for Folders entities
                 */
                $translatedData = $data->getTranslatedFoldersByTranslation($context['translation'])->first() ?:
                    $data->getTranslatedFoldersByDefaultTranslation();
                if ($translatedData instanceof FolderTranslation) {
                    $normalized['name'] = $translatedData->getName();
                }
            }
        }

        return $normalized;
    }
}
