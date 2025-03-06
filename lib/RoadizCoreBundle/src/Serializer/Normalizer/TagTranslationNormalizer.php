<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\Normalizer;

use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\CoreBundle\Entity\TagTranslation;
use RZ\Roadiz\Documents\Models\DocumentInterface;

/**
 * Override Tag default normalization.
 */
final class TagTranslationNormalizer extends AbstractPathNormalizer
{
    /**
     * @return array|\ArrayObject|bool|float|int|mixed|string|null
     *
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): mixed
    {
        $data = $this->decorated->normalize($object, $format, $context);
        if (
            $object instanceof Tag
            && is_array($data)
            && isset($context['translation'])
            && $context['translation'] instanceof TranslationInterface
        ) {
            $this->stopwatch->start('normalizeTag', 'serializer');
            /** @var array<string> $serializationGroups */
            $serializationGroups = isset($context['groups']) && is_array($context['groups']) ? $context['groups'] : [];
            /*
             * Always falls back on default translation if no translation is found for Tags entities
             */
            $translatedData = $object->getTranslatedTagsByTranslation($context['translation'])->first() ?:
                $object->getTranslatedTagsByDefaultTranslation();
            if ($translatedData instanceof TagTranslation) {
                $data['name'] = $translatedData->getName();
                $data['description'] = $translatedData->getDescription();

                if (\in_array('tag_documents', $serializationGroups, true)) {
                    $documentsContext = $context;
                    $documentsContext['groups'] = ['document_display'];
                    $data['documents'] = array_map(function (DocumentInterface $document) use ($format, $documentsContext) {
                        return $this->decorated->normalize($document, $format, $documentsContext);
                    }, $translatedData->getDocuments());
                }
            }
            $this->stopwatch->stop('normalizeTag');
        }

        return $data;
    }
}
