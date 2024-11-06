<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use RZ\Roadiz\Core\AbstractEntities\AbstractEntity;
use RZ\Roadiz\CoreBundle\Model\AttributeInterface;
use RZ\Roadiz\CoreBundle\Model\AttributeTrait;
use RZ\Roadiz\CoreBundle\Model\RealmInterface;
use RZ\Roadiz\CoreBundle\Repository\AttributeRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Range;

#[
    ORM\Entity(repositoryClass: AttributeRepository::class),
    ORM\Table(name: 'attributes'),
    ORM\Index(columns: ['code']),
    ORM\Index(columns: ['type']),
    ORM\Index(columns: ['searchable']),
    ORM\Index(columns: ['weight']),
    ORM\Index(columns: ['color']),
    ORM\Index(columns: ['group_id']),
    ORM\HasLifecycleCallbacks,
    UniqueEntity(fields: ['code']),
]
class Attribute extends AbstractEntity implements AttributeInterface
{
    use AttributeTrait;

    /**
     * @var Collection<int, AttributeDocuments>
     */
    #[
        ORM\OneToMany(
            mappedBy: 'attribute',
            targetEntity: AttributeDocuments::class,
            cascade: ['persist', 'merge'],
            orphanRemoval: true
        ),
        ORM\OrderBy(['position' => 'ASC']),
        Serializer\Exclude,
        Serializer\Type("ArrayCollection<RZ\Roadiz\CoreBundle\Entity\AttributeDocuments>"),
        SymfonySerializer\Ignore
    ]
    protected Collection $attributeDocuments;

    #[ORM\ManyToOne(targetEntity: Realm::class)]
    #[ORM\JoinColumn(
        name: 'realm_id',
        referencedColumnName: 'id',
        unique: false,
        nullable: true,
        onDelete: 'SET NULL'
    )]
    #[SymfonySerializer\Ignore]
    #[Serializer\Exclude]
    private ?RealmInterface $defaultRealm = null;

    /**
     * @var int absolute weight for sorting attributes in filtered lists
     */
    #[
        ORM\Column(type: 'integer', nullable: false, options: ['default' => 0]),
        Serializer\Type('integer'),
        Serializer\Groups(['attribute', 'node', 'nodes_sources']),
        SymfonySerializer\Groups(['attribute', 'node', 'nodes_sources']),
        ApiFilter(OrderFilter::class),
        Range(min: 0, max: 9999),
        NotNull,
    ]
    protected int $weight = 0;

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

    public function setAttributeDocuments(Collection $attributeDocuments): Attribute
    {
        $this->attributeDocuments = $attributeDocuments;

        return $this;
    }

    public function getDefaultRealm(): ?RealmInterface
    {
        return $this->defaultRealm;
    }

    public function setDefaultRealm(?RealmInterface $defaultRealm): Attribute
    {
        $this->defaultRealm = $defaultRealm;

        return $this;
    }

    public function getWeight(): int
    {
        return $this->weight;
    }

    public function setWeight(?int $weight): Attribute
    {
        $this->weight = $weight ?? 0;

        return $this;
    }

    /**
     * @return Collection<int, Document>
     */
    #[
        Serializer\VirtualProperty(),
        Serializer\Groups(['attribute', 'node', 'nodes_sources']),
        SymfonySerializer\Groups(['attribute', 'node', 'nodes_sources']),
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
