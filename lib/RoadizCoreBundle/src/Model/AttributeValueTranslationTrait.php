<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Model;

use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

trait AttributeValueTranslationTrait
{
    #[
        ORM\ManyToOne(targetEntity: TranslationInterface::class),
        ORM\JoinColumn(name: "translation_id", referencedColumnName: "id", onDelete: "CASCADE"),
        Serializer\Groups(["attribute", "node", "nodes_sources"]),
        Serializer\Type("RZ\Roadiz\Core\AbstractEntities\TranslationInterface"),
        Serializer\Accessor(getter: "getTranslation", setter: "setTranslation")
    ]
    protected ?TranslationInterface $translation = null;

    #[
        ORM\Column(type: "string", length: 255, unique: false, nullable: true),
        Serializer\Groups(["attribute", "node", "nodes_sources"]),
        Serializer\Type("string")
    ]
    protected ?string $value = null;

    #[
        ORM\ManyToOne(targetEntity: AttributeValueInterface::class, cascade: ["persist"], inversedBy: "attributeValueTranslations"),
        ORM\JoinColumn(name: "attribute_value", referencedColumnName: "id", onDelete: "CASCADE"),
        Serializer\Exclude
    ]
    protected ?AttributeValueInterface $attributeValue = null;

    /**
     * @return mixed|null
     * @throws \Exception
     */
    public function getValue()
    {
        if (null === $this->value) {
            return null;
        }
        switch ($this->getAttributeValue()->getType()) {
            case AttributeInterface::DECIMAL_T:
                return (float) $this->value;
            case AttributeInterface::INTEGER_T:
                return (int) $this->value;
            case AttributeInterface::BOOLEAN_T:
                return (bool) $this->value;
            case AttributeInterface::DATETIME_T:
            case AttributeInterface::DATE_T:
                return $this->value ? new \DateTime($this->value) : null;
            default:
                return $this->value;
        }
    }

    /**
     * @param mixed|null $value
     *
     * @return static
     */
    public function setValue($value)
    {
        if (null === $value) {
            $this->value = null;
        }
        switch ($this->getAttributeValue()->getType()) {
            case AttributeInterface::EMAIL_T:
                if (false === filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    throw new \InvalidArgumentException('Email is not valid');
                }
                $this->value = (string) $value;
                return $this;
            case AttributeInterface::DATETIME_T:
            case AttributeInterface::DATE_T:
                if ($value instanceof \DateTime) {
                    $this->value = $value->format('Y-m-d H:i:s');
                } else {
                    $this->value = (string) $value;
                }
                return $this;
            default:
                $this->value = (string) $value;
                return $this;
        }
    }

    /**
     * @param TranslationInterface $translation
     *
     * @return static
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
     * @return AttributeValueInterface
     */
    public function getAttributeValue(): AttributeValueInterface
    {
        return $this->attributeValue;
    }

    /**
     * @param AttributeValueInterface $attributeValue
     *
     * @return static
     */
    public function setAttributeValue(AttributeValueInterface $attributeValue)
    {
        $this->attributeValue = $attributeValue;
        return $this;
    }

    /**
     * @return AttributeInterface
     */
    public function getAttribute(): ?AttributeInterface
    {
        return $this->getAttributeValue()->getAttribute();
    }
}
