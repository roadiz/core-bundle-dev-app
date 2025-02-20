<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\Core\AbstractEntities\AbstractEntity;
use RZ\Roadiz\CoreBundle\Enum\FieldType;
use RZ\Roadiz\CoreBundle\Repository\SettingRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation as Serializer;
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
    UniqueEntity(fields: ['name']),
]
class Setting extends AbstractEntity
{
    use FieldTypeTrait;

    /**
     * @var array<int, FieldType>
     */
    #[Serializer\Ignore]
    public static array $availableTypes = [
        FieldType::STRING_T,
        FieldType::DATETIME_T,
        FieldType::TEXT_T,
        FieldType::MARKDOWN_T,
        FieldType::BOOLEAN_T,
        FieldType::INTEGER_T,
        FieldType::DECIMAL_T,
        FieldType::EMAIL_T,
        FieldType::DOCUMENTS_T,
        FieldType::COLOUR_T,
        FieldType::JSON_T,
        FieldType::CSS_T,
        FieldType::YAML_T,
        FieldType::ENUM_T,
        FieldType::MULTIPLE_T,
    ];

    #[ORM\Column(type: 'string', length: 250, unique: true)]
    #[Serializer\Groups(['setting', 'nodes_sources'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 250)]
    private string $name = '';

    #[ORM\Column(type: 'text', unique: false, nullable: true)]
    #[Serializer\Groups(['setting'])]
    private ?string $description = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Serializer\Groups(['setting', 'nodes_sources'])]
    private ?string $value = null;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => true])]
    #[Serializer\Groups(['setting'])]
    private bool $visible = true;

    #[ORM\ManyToOne(
        targetEntity: SettingGroup::class,
        cascade: ['persist', 'merge'],
        inversedBy: 'settings'
    )]
    #[ORM\JoinColumn(name: 'setting_group_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[Serializer\Groups(['setting'])]
    private ?SettingGroup $settingGroup = null;

    #[ORM\Column(
        type: Types::SMALLINT,
        nullable: false,
        enumType: FieldType::class,
        options: ['default' => FieldType::STRING_T]
    )]
    #[Serializer\Groups(['setting'])]
    private FieldType $type = FieldType::STRING_T;

    /**
     * Available values for ENUM and MULTIPLE setting types.
     */
    #[ORM\Column(name: 'defaultValues', type: 'text', nullable: true)]
    #[Serializer\Groups(['setting'])]
    private ?string $defaultValues = null;

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
    #[Serializer\Ignore]
    public function getValue(): string|bool|\DateTime|int|null
    {
        if (FieldType::BOOLEAN_T === $this->getType()) {
            return (bool) $this->value;
        }

        if (null !== $this->value) {
            if (FieldType::DATETIME_T === $this->getType()) {
                return new \DateTime($this->value);
            }
            if (FieldType::DOCUMENTS_T === $this->getType()) {
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
