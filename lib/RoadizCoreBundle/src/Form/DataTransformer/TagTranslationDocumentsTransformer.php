<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ObjectManager;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\TagTranslation;
use RZ\Roadiz\CoreBundle\Entity\TagTranslationDocuments;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Class TagTranslationDocumentsTransformer.
 */
class TagTranslationDocumentsTransformer implements DataTransformerInterface
{
    private ObjectManager $manager;
    private TagTranslation $tagTranslation;

    public function __construct(ObjectManager $manager, TagTranslation $tagTranslation)
    {
        $this->manager = $manager;
        $this->tagTranslation = $tagTranslation;
    }

    /**
     * Transform TagTranslationDocuments join entities
     * to Document entities for displaying in document VueJS component.
     *
     * @param TagTranslationDocuments[]|null $value
     *
     * @return Document[]
     */
    public function transform(mixed $value): array
    {
        if (empty($value)) {
            return [];
        }
        $documents = [];
        foreach ($value as $tagTranslationDocument) {
            $documents[] = $tagTranslationDocument->getDocument();
        }

        return $documents;
    }

    /**
     * @param array<int> $value
     */
    public function reverseTransform(mixed $value): ArrayCollection
    {
        if (!$value) {
            return new ArrayCollection();
        }

        $documents = new ArrayCollection();
        $position = 0;
        foreach ($value as $documentId) {
            $document = $this->manager
                ->getRepository(Document::class)
                ->find($documentId)
            ;
            if (null === $document) {
                throw new TransformationFailedException(sprintf('A document with id "%s" does not exist!', $documentId));
            }

            $ttd = new TagTranslationDocuments($this->tagTranslation, $document);
            $ttd->setPosition($position);
            $this->manager->persist($ttd);
            $documents->add($ttd);

            ++$position;
        }

        return $documents;
    }
}
