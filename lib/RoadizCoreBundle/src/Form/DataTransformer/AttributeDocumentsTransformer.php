<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Attribute;
use RZ\Roadiz\CoreBundle\Entity\AttributeDocuments;
use RZ\Roadiz\CoreBundle\Entity\Document;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

final readonly class AttributeDocumentsTransformer implements DataTransformerInterface
{
    public function __construct(private ManagerRegistry $managerRegistry, private Attribute $attribute)
    {
    }

    /**
     * Transform AttributeDocuments join entities
     * to Document entities for displaying in document VueJS component.
     *
     * @param AttributeDocuments[]|null $value
     *
     * @return Document[]
     */
    #[\Override]
    public function transform(mixed $value): array
    {
        if (empty($value)) {
            return [];
        }
        $documents = [];
        foreach ($value as $attributeDocument) {
            $documents[] = $attributeDocument->getDocument();
        }

        return $documents;
    }

    /**
     * @param array $value
     */
    #[\Override]
    public function reverseTransform(mixed $value): ArrayCollection
    {
        if (!$value) {
            return new ArrayCollection();
        }

        $documents = new ArrayCollection();
        $position = 0;
        foreach ($value as $documentId) {
            $document = $this->managerRegistry
                ->getRepository(Document::class)
                ->find($documentId)
            ;
            if (null === $document) {
                throw new TransformationFailedException(sprintf('A document with id "%s" does not exist!', $documentId));
            }

            $ttd = new AttributeDocuments($this->attribute, $document);
            $ttd->setPosition($position);
            $this->managerRegistry->getManagerForClass(class: AttributeDocuments::class)?->persist($ttd);
            $documents->add($ttd);

            ++$position;
        }

        return $documents;
    }
}
