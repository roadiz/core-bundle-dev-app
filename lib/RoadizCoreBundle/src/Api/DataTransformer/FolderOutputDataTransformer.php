<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Api\Dto\FolderOutput;
use RZ\Roadiz\CoreBundle\Entity\Folder;
use RZ\Roadiz\CoreBundle\Entity\FolderTranslation;
use RZ\Roadiz\Documents\Models\FolderInterface;

/**
 * @deprecated Use FolderNormalizer
 */
class FolderOutputDataTransformer implements DataTransformerInterface
{
    /**
     * @inheritDoc
     */
    public function transform($data, string $to, array $context = []): object
    {
        if (!$data instanceof Folder) {
            throw new \InvalidArgumentException('Data to transform must be instance of ' . FolderInterface::class);
        }
        $output = new FolderOutput();
        $output->name = $data->getName();
        $output->slug = $data->getFolderName();
        $output->visible = $data->getVisible();
        $output->position = $data->getPosition();

        if (isset($context['translation']) && $context['translation'] instanceof TranslationInterface) {
            $translatedData = $data->getTranslatedFoldersByTranslation($context['translation'])->first() ?: null;
            if ($translatedData instanceof FolderTranslation) {
                $output->name = $translatedData->getName();
            }
        }
        return $output;
    }

    /**
     * @inheritDoc
     */
    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        return FolderOutput::class === $to && $data instanceof FolderInterface;
    }
}
