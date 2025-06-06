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
    /**
     * @return array|\ArrayObject|bool|float|int|mixed|string|null
     *
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    #[\Override]
    public function normalize(mixed $object, ?string $format = null, array $context = []): mixed
    {
        $data = $this->decorated->normalize($object, $format, $context);
        if ($object instanceof Folder && is_array($data)) {
            if (isset($context['translation']) && $context['translation'] instanceof TranslationInterface) {
                /*
                 * Always falls back on default translation if no translation is found for Folders entities
                 */
                $translatedData = $object->getTranslatedFoldersByTranslation($context['translation'])->first() ?:
                    $object->getTranslatedFoldersByDefaultTranslation();
                if ($translatedData instanceof FolderTranslation) {
                    $data['name'] = $translatedData->getName();
                }
            }
        }

        return $data;
    }
}
