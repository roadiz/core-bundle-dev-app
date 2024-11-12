<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use RZ\Roadiz\Core\AbstractEntities\AbstractEntity;
use RZ\Roadiz\Core\AbstractEntities\AbstractField;
use RZ\Roadiz\CoreBundle\Repository\SettingRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\String\UnicodeString;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Settings entity are a simple key-value configuration system.
 */
#[
    ORM\Entity(repositoryClass: SettingRepository::class),
    ORM\Table(name: 'settings'),
    ORM\Index(columns: ['type']),
    ORM\Index(columns: ['name']),
    ORM\Index(columns: ['visible']),
    UniqueEntity(fields: ['name'])
]
class Setting extends AbstractEntity
{
    /**
     * Associates custom form field type to a readable string.
     *
     * These string will be used as translation key.
     *
     * @var array<int, string>
     */
    #[SymfonySerializer\Ignore]
    #[Serializer\Exclude]
    public static array $typeToHuman = [
        AbstractField::STRING_T => 'string.type',
        AbstractField::DATETIME_T => 'date-time.type',
        AbstractField::TEXT_T => 'text.type',
        AbstractField::MARKDOWN_T => 'markdown.type',
        AbstractField::BOOLEAN_T => 'boolean.type',
        AbstractField::INTEGER_T => 'integer.type',
        AbstractField::DECIMAL_T => 'decimal.type',
        AbstractField::EMAIL_T => 'email.type',
        AbstractField::DOCUMENTS_T => 'documents.type',
        AbstractField::COLOUR_T => 'colour.type',
        AbstractField::JSON_T => 'json.type',
        AbstractField::CSS_T => 'css.type',
        AbstractField::YAML_T => 'yaml.type',
        AbstractField::ENUM_T => 'single-choice.type',
        AbstractField::MULTIPLE_T => 'multiple-choice.type',
    ];

    #[ORM\Column(type: 'string', length: 250, unique: true)]
    #[SymfonySerializer\Groups(['setting', 'nodes_sources'])]
    #[Serializer\Groups(['setting', 'nodes_sources'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 250)]
    private string $name = '';

    #[ORM\Column(type: 'text', unique: false, nullable: true)]
    #[SymfonySerializer\Groups(['setting'])]
    #[Serializer\Groups(['setting'])]
    private ?string $description = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[SymfonySerializer\Groups(['setting', 'nodes_sources'])]
    #[Serializer\Groups(['setting', 'nodes_sources'])]
    private ?string $value = null;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => true])]
    #[SymfonySerializer\Groups(['setting'])]
    #[Serializer\Groups(['setting'])]
    private bool $visible = true;

    #[ORM\ManyToOne(
        targetEntity: SettingGroup::class,
        cascade: ['persist', 'merge'],
        inversedBy: 'settings'
    )]
    #[ORM\JoinColumn(name: 'setting_group_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[SymfonySerializer\Groups(['setting'])]
    #[Serializer\Groups(['setting'])]
    #[Serializer\AccessType(type: 'public_method')]
    #[Serializer\Accessor(getter: 'getSettingGroup', setter: 'setSettingGroup')]
    private ?SettingGroup $settingGroup;

    /**
     * Value types.
     * Use NodeTypeField types constants.
     */
    #[ORM\Column(type: 'integer')]
    #[SymfonySerializer\Groups(['setting'])]
    #[Serializer\Groups(['setting'])]
    private int $type = AbstractField::STRING_T;

    /**
     * Available values for ENUM and MULTIPLE setting types.
     */
    #[ORM\Column(name: 'defaultValues', type: 'text', nullable: true)]
    #[SymfonySerializer\Groups(['setting'])]
    #[Serializer\Groups(['setting'])]
    private ?string $defaultValues;

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return $this
     */
    public function setName(?string $name): self
    {
        $this->name = trim(\mb_strtolower($name ?? ''));
        $this->name = (new UnicodeString($this->name))
            ->ascii()
            ->toString();
        $this->name = preg_replace('#([^a-z])#', '_', $this->name);

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): Setting
    {
        $this->description = $description;

        return $this;
    }

    public function getRawValue(): ?string
    {
        return $this->value;
    }

    /**
     * @throws \Exception
     */
    #[SymfonySerializer\Ignore]
    public function getValue(): string|bool|\DateTime|int|null
    {
        if (AbstractField::BOOLEAN_T == $this->getType()) {
            return (bool) $this->value;
        }

        if (null !== $this->value) {
            if (AbstractField::DATETIME_T == $this->getType()) {
                return new \DateTime($this->value);
            }
            if (AbstractField::DOCUMENTS_T == $this->getType()) {
                return (int) $this->value;
            }
        }

        return $this->value;
    }

    /**
     * @return $this
     */
    public function setValue(mixed $value): self
    {
        if (null === $value) {
            $this->value = null;
        } elseif ($value instanceof \DateTimeInterface) {
            $this->value = $value->format('c'); // $value is instance of \DateTime
        } else {
            $this->value = (string) $value;
        }

        return $this;
    }

    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @return $this
     */
    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    /**
     * @return $this
     */
    public function setVisible(bool $visible): self
    {
        $this->visible = $visible;

        return $this;
    }

    public function getSettingGroup(): ?SettingGroup
    {
        return $this->settingGroup;
    }

    /**
     * @return $this
     */
    public function setSettingGroup(?SettingGroup $settingGroup): self
    {
        $this->settingGroup = $settingGroup;

        return $this;
    }

    public function getDefaultValues(): ?string
    {
        return $this->defaultValues;
    }

    public function setDefaultValues(?string $defaultValues): self
    {
        $this->defaultValues = $defaultValues;

        return $this;
    }
}
