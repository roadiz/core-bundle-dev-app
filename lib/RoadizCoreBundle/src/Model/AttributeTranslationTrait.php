<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

trait AttributeTranslationTrait
{
    #[
        ORM\ManyToOne(targetEntity: TranslationInterface::class),
        ORM\JoinColumn(name: 'translation_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE'),
        SymfonySerializer\Groups(['attribute', 'attribute:export', 'node', 'nodes_sources']),
    ]
    protected TranslationInterface $translation;

    #[
        ORM\Column(type: 'string', length: 250, unique: false, nullable: false),
        SymfonySerializer\Groups(['attribute', 'attribute:export', 'node', 'nodes_sources']),
        Assert\Length(max: 250)
    ]
    protected string $label = '';

    /**
     * @var array<string>|null
     */
    #[
        ORM\Column(type: 'simple_array', unique: false, nullable: true),
        SymfonySerializer\Groups(['attribute', 'attribute:export']),
    ]
    protected ?array $options = [];

    #[
        ORM\ManyToOne(targetEntity: AttributeInterface::class, cascade: ['persist'], inversedBy: 'attributeTranslations'),
        ORM\JoinColumn(name: 'attribute_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE'),
        SymfonySerializer\Ignore,
    ]
    protected AttributeInterface $attribute;

    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * @return $this
     */
    public function setLabel(?string $label): self
    {
        $this->label = null !== $label ? trim($label) : null;

        return $this;
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

    public function getAttribute(): AttributeInterface
    {
        return $this->attribute;
    }

    /**
     * @return $this
     */
    public function setAttribute(AttributeInterface $attribute): self
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
    public function setOptions(?array $options): self
    {
        $this->options = $options;

        return $this;
    }
}
