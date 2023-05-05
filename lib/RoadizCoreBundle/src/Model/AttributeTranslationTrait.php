<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Model;

use JMS\Serializer\Annotation as Serializer;
use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;

trait AttributeTranslationTrait
{
    #[
        ORM\ManyToOne(targetEntity: TranslationInterface::class),
        ORM\JoinColumn(onDelete: "CASCADE"),
        Serializer\Groups(["attribute", "node", "nodes_sources"]),
        Serializer\Type("RZ\Roadiz\Core\AbstractEntities\TranslationInterface"),
        Serializer\Accessor(getter: "getTranslation", setter: "setTranslation")
    ]
    protected ?TranslationInterface $translation = null;

    #[
        ORM\Column(type: "string", unique: false, nullable: false),
        Serializer\Groups(["attribute", "node", "nodes_sources"]),
        Serializer\Type("string")
    ]
    protected string $label = '';

    /**
     * @var array<string>|null
     */
    #[
        ORM\Column(type: "simple_array", unique: false, nullable: true),
        Serializer\Groups(["attribute"]),
        Serializer\Type("array")
    ]
    protected ?array $options = [];

    #[
        ORM\ManyToOne(targetEntity: AttributeInterface::class, cascade: ["persist"], inversedBy: "attributeTranslations"),
        ORM\JoinColumn(referencedColumnName: "id", onDelete: "CASCADE"),
        Serializer\Exclude
    ]
    protected ?AttributeInterface $attribute = null;

    /**
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * @param string|null $label
     *
     * @return $this
     */
    public function setLabel(?string $label)
    {
        $this->label = null !== $label ? trim($label) : null;
        return $this;
    }

    /**
     * @param TranslationInterface $translation
     *
     * @return $this
     */
    public function setTranslation(TranslationInterface $translation)
    {
        $this->translation = $translation;
        return $this;
    }

    /**
     * @return TranslationInterface|null
     */
    public function getTranslation(): ?TranslationInterface
    {
        return $this->translation;
    }

    /**
     * @return AttributeInterface
     */
    public function getAttribute(): AttributeInterface
    {
        return $this->attribute;
    }

    /**
     * @param AttributeInterface $attribute
     *
     * @return $this
     */
    public function setAttribute(AttributeInterface $attribute)
    {
        $this->attribute = $attribute;
        return $this;
    }


    /**
     * @return array<string>|null
     */
    public function getOptions(): ?array
    {
        return $this->options;
    }

    /**
     * @param array<string>|null $options
     *
     * @return $this
     */
    public function setOptions(?array $options)
    {
        $this->options = $options;
        return $this;
    }
}
