<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use Symfony\Component\Serializer\Attribute as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

trait AttributeValueTranslationTrait
{
    #[
        ORM\ManyToOne(targetEntity: TranslationInterface::class),
        ORM\JoinColumn(name: 'translation_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE'),
        Serializer\Groups(['attribute', 'node', 'nodes_sources']),
    ]
    protected TranslationInterface $translation;

    #[
        ORM\Column(type: 'string', length: 255, unique: false, nullable: true),
        Serializer\Groups(['attribute', 'node', 'nodes_sources']),
        Assert\Length(max: 255)
    ]
    protected ?string $value = null;

    #[
        ORM\ManyToOne(targetEntity: AttributeValueInterface::class, cascade: ['persist'], inversedBy: 'attributeValueTranslations'),
        ORM\JoinColumn(name: 'attribute_value', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE'),
        Serializer\Ignore
    ]
    protected AttributeValueInterface $attributeValue;

    /**
     * @throws \Exception
     */
    public function getValue(): bool|\DateTime|float|int|string|null
    {
        if (null === $this->value) {
            return null;
        }

        return match ($this->getAttributeValue()->getType()) {
            AttributeInterface::DECIMAL_T => (float) $this->value,
            AttributeInterface::INTEGER_T => (int) $this->value,
            AttributeInterface::BOOLEAN_T => (bool) $this->value,
            AttributeInterface::DATETIME_T, AttributeInterface::DATE_T => $this->value ? new \DateTime($this->value) : null,
            default => $this->value,
        };
    }

    /**
     * @param mixed|null $value
     *
     * @return $this
     */
    public function setValue(mixed $value): self
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
                if ($value instanceof \DateTimeInterface) {
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
     * @return $this
     */
    public function setTranslation(TranslationInterface $translation): self
    {
        $this->translation = $translation;

        return $this;
    }

    public function getTranslation(): TranslationInterface
    {
        return $this->translation;
    }

    public function getAttributeValue(): AttributeValueInterface
    {
        return $this->attributeValue;
    }

    /**
     * @return $this
     */
    public function setAttributeValue(AttributeValueInterface $attributeValue): self
    {
        $this->attributeValue = $attributeValue;

        return $this;
    }

    public function getAttribute(): AttributeInterface
    {
        return $this->getAttributeValue()->getAttribute();
    }
}
