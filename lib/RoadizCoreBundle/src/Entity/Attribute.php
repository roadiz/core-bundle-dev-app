<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use RZ\Roadiz\CoreBundle\Repository\AttributeRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use RZ\Roadiz\CoreBundle\Model\AttributeInterface;
use RZ\Roadiz\CoreBundle\Model\AttributeTrait;
use RZ\Roadiz\Core\AbstractEntities\AbstractEntity;

/**
 * @package RZ\Roadiz\CoreBundle\Entity
 */
#[
    ORM\Entity(repositoryClass: AttributeRepository::class),
    ORM\Table(name: "attributes"),
    ORM\Index(columns: ["code"]),
    ORM\Index(columns: ["type"]),
    ORM\Index(columns: ["searchable"]),
    ORM\Index(columns: ["group_id"]),
    ORM\HasLifecycleCallbacks,
    UniqueEntity(fields: ["code"]),
]
class Attribute extends AbstractEntity implements AttributeInterface
{
    use AttributeTrait;

    /**
     * @var Collection<int, AttributeDocuments>
     */
    #[
        ORM\OneToMany(
            mappedBy: "attribute",
            targetEntity: AttributeDocuments::class,
            cascade: ["persist", "merge"],
            orphanRemoval: true
        ),
        ORM\OrderBy(["position" => "ASC"]),
        Serializer\Exclude,
        Serializer\Type("ArrayCollection<RZ\Roadiz\CoreBundle\Entity\AttributeDocuments>"),
        SymfonySerializer\Ignore
    ]
    protected Collection $attributeDocuments;

    public function __construct()
    {
        $this->attributeTranslations = new ArrayCollection();
        $this->attributeValues = new ArrayCollection();
        $this->attributeDocuments = new ArrayCollection();
    }

    /**
     * @return Collection<int, AttributeDocuments>
     */
    public function getAttributeDocuments(): Collection
    {
        return $this->attributeDocuments;
    }

    /**
     * @param Collection $attributeDocuments
     *
     * @return Attribute
     */
    public function setAttributeDocuments(Collection $attributeDocuments): Attribute
    {
        $this->attributeDocuments = $attributeDocuments;

        return $this;
    }

    /**
     * @return Collection<int, Document>
     */
    #[
        Serializer\VirtualProperty(),
        Serializer\Groups(["attribute", "node", "nodes_sources"]),
        SymfonySerializer\Groups(["attribute", "node", "nodes_sources"]),
    ]
    public function getDocuments(): Collection
    {
        /** @var Collection<int, Document> $values */
        $values = $this->attributeDocuments->map(function (AttributeDocuments $attributeDocuments) {
            return $attributeDocuments->getDocument();
        })->filter(function (?Document $document) {
            return null !== $document;
        });
        return $values; // phpstan does not understand filtering null values
    }
}
