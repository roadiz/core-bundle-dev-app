<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use ApiPlatform\Doctrine\Orm\Filter as BaseFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Serializer\Filter\PropertyFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use RZ\Roadiz\Core\AbstractEntities\AbstractPositioned;
use RZ\Roadiz\CoreBundle\Model\AttributableInterface;
use RZ\Roadiz\CoreBundle\Model\AttributeValueInterface;
use RZ\Roadiz\CoreBundle\Model\AttributeValueTrait;
use RZ\Roadiz\CoreBundle\Model\AttributeValueTranslationInterface;
use RZ\Roadiz\CoreBundle\Model\RealmInterface;
use RZ\Roadiz\CoreBundle\Repository\AttributeValueRepository;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;

#[
    ORM\Entity(repositoryClass: AttributeValueRepository::class),
    ORM\Table(name: "attribute_values"),
    ORM\Index(columns: ["attribute_id", "node_id"]),
    ORM\Index(columns: ["node_id", "position"], name: "idx_attribute_value_node_position"),
    ORM\Index(columns: ["position"], name: "idx_attribute_value_position"),
    ORM\HasLifecycleCallbacks,
    ApiFilter(PropertyFilter::class),
    ApiFilter(BaseFilter\OrderFilter::class, properties: [
        "position",
    ]),
]
class AttributeValue extends AbstractPositioned implements AttributeValueInterface
{
    use AttributeValueTrait;

    /**
     * @var Node|null
     */
    #[
        ORM\ManyToOne(targetEntity: Node::class, inversedBy: "attributeValues"),
        ORM\JoinColumn(name: "node_id", onDelete: "CASCADE"),
        Serializer\Groups(["attribute_node"]),
        SymfonySerializer\Groups(["attribute_node"]),
        SymfonySerializer\MaxDepth(1),
        ApiFilter(BaseFilter\SearchFilter::class, properties: [
            "node" => "exact",
            "node.id" => "exact",
            "node.nodeName" => "exact",
            "node.nodeType" => "exact",
            "node.nodeType.name" => "exact"
        ]),
        ApiFilter(BaseFilter\BooleanFilter::class, properties: [
            "node.visible"
        ])
    ]
    protected ?Node $node = null;

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
    private ?RealmInterface $realm = null;

    public function __construct()
    {
        $this->attributeValueTranslations = new ArrayCollection();
    }

    /*
     * Override method to add serialization groups and
     * enable RZ\Roadiz\CoreBundle\Serializer\Normalizer\AttributeValueNormalizer
     * to perform a custom serialization
     */
    #[SymfonySerializer\Groups(['position', 'attribute', 'node_attributes'])]
    public function getPosition(): float
    {
        return $this->position;
    }

    /**
     * @inheritDoc
     */
    public function getAttributable(): ?AttributableInterface
    {
        return $this->node;
    }

    /**
     * @inheritDoc
     */
    public function setAttributable(?AttributableInterface $attributable)
    {
        if ($attributable instanceof Node) {
            $this->node = $attributable;
            return $this;
        }
        throw new \InvalidArgumentException('Attributable have to be an instance of Node.');
    }

    /**
     * @return Node|null
     */
    public function getNode(): ?Node
    {
        return $this->node;
    }

    /**
     * @param Node|null $node
     *
     * @return AttributeValue
     */
    public function setNode(?Node $node): AttributeValue
    {
        $this->node = $node;

        return $this;
    }

    public function getRealm(): ?RealmInterface
    {
        return $this->realm;
    }

    public function setRealm(?RealmInterface $realm): AttributeValue
    {
        $this->realm = $realm;
        return $this;
    }

    /**
     * After clone method.
     *
     * Clone current node and ist relations.
     */
    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
            $attributeValueTranslations = $this->getAttributeValueTranslations();
            $this->attributeValueTranslations = new ArrayCollection();
            /** @var AttributeValueTranslationInterface $attributeValueTranslation */
            foreach ($attributeValueTranslations as $attributeValueTranslation) {
                $cloneAttributeValueTranslation = clone $attributeValueTranslation;
                $cloneAttributeValueTranslation->setAttributeValue($this);
                $this->attributeValueTranslations->add($cloneAttributeValueTranslation);
            }
        }
    }
}
