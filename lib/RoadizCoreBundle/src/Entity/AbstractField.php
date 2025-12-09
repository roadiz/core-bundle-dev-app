<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\Core\AbstractEntities\PositionedInterface;
use RZ\Roadiz\Core\AbstractEntities\PositionedTrait;
use RZ\Roadiz\Core\AbstractEntities\SequentialIdTrait;
use RZ\Roadiz\CoreBundle\Enum\FieldType;
use RZ\Roadiz\Utils\StringHandler;
use Symfony\Component\Serializer\Attribute as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Yaml\Yaml;

#[
    ORM\MappedSuperclass,
    ORM\Table,
    ORM\HasLifecycleCallbacks,
    ORM\Index(columns: ['position']),
    ORM\Index(columns: ['group_name']),
    ORM\Index(columns: ['group_name_canonical'])
]
abstract class AbstractField implements PositionedInterface, PersistableInterface
{
    use SequentialIdTrait;
    use PositionedTrait;
    use FieldTypeTrait;

    #[
        ORM\Column(name: 'group_name', type: 'string', length: 250, nullable: true),
        Assert\Length(max: 250),
        Serializer\Groups(['node_type', 'node_type:import', 'setting']),
    ]
    protected ?string $groupName = null;

    #[
        ORM\Column(name: 'group_name_canonical', type: 'string', length: 250, nullable: true),
        Serializer\Groups(['node_type', 'setting']),
        Assert\Length(max: 250),
    ]
    protected ?string $groupNameCanonical = null;

    #[
        ORM\Column(type: 'string', length: 250),
        Serializer\Groups(['node_type', 'node_type:import', 'setting']),
        Assert\Length(max: 250),
        Assert\NotBlank(),
        Assert\NotNull()
    ]
    protected string $name;

    #[
        ORM\Column(type: 'string', length: 250),
        Serializer\Groups(['node_type', 'node_type:import', 'setting']),
        Assert\Length(max: 250),
        Assert\NotBlank(),
        Assert\NotNull()
    ]
    // @phpstan-ignore-next-line
    protected ?string $label = null;

    #[
        ORM\Column(type: 'string', length: 250, nullable: true),
        Serializer\Groups(['node_type', 'node_type:import', 'setting']),
        Assert\Length(max: 250),
    ]
    protected ?string $placeholder = null;

    #[
        ORM\Column(type: 'text', nullable: true),
        Serializer\Groups(['node_type', 'node_type:import', 'setting']),
    ]
    protected ?string $description = null;

    #[
        ORM\Column(name: 'default_values', type: 'text', nullable: true),
        Serializer\Groups(['node_type', 'setting']),
    ]
    protected ?string $defaultValues = null;

    #[
        ORM\Column(
            type: Types::SMALLINT,
            nullable: false,
            enumType: FieldType::class,
            options: ['default' => FieldType::STRING_T]
        ),
        Serializer\Groups(['node_type', 'setting']),
    ]
    protected FieldType $type = FieldType::STRING_T;

    /**
     * If current field data should be expanded (for choices and country types).
     */
    #[
        ORM\Column(name: 'expanded', type: 'boolean', nullable: false, options: ['default' => false]),
        Serializer\Groups(['node_type', 'node_type:import', 'setting']),
    ]
    protected bool $expanded = false;

    public function __construct()
    {
        $this->label = 'Untitled field';
        $this->name = 'untitled_field';
    }

    /**
     * @return string Camel case field name
     */
    public function getVarName(): string
    {
        return StringHandler::camelCase($this->getName());
    }

    /**
     * @return string $name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return $this
     */
    public function setName(?string $name): static
    {
        $this->name = StringHandler::variablize($name ?? '');

        return $this;
    }

    /**
     * @return string Camel case getter method name
     */
    public function getGetterName(): string
    {
        return StringHandler::camelCase('get '.$this->getName());
    }

    /**
     * @return string Camel case setter method name
     */
    public function getSetterName(): string
    {
        return StringHandler::camelCase('set '.$this->getName());
    }

    public function getLabel(): string
    {
        return $this->label ?? '';
    }

    /**
     * @return $this
     */
    public function setLabel(?string $label): static
    {
        $this->label = $label ?? '';

        return $this;
    }

    public function getPlaceholder(): ?string
    {
        return $this->placeholder;
    }

    /**
     * @return $this
     */
    public function setPlaceholder(?string $placeholder): static
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return $this
     */
    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDefaultValues(): ?string
    {
        return $this->defaultValues;
    }

    /**
     * @return $this
     */
    public function setDefaultValues(?string $defaultValues): static
    {
        $this->defaultValues = $defaultValues;

        return $this;
    }

    public function getDefaultValuesAsArray(): array
    {
        $defaultValues = Yaml::parse($this->defaultValues ?? '') ?? '';

        return is_array($defaultValues) ? $defaultValues : [];
    }

    /**
     * Gets the value of groupName.
     */
    public function getGroupName(): ?string
    {
        return $this->groupName;
    }

    /**
     * Sets the value of groupName.
     *
     * @param string|null $groupName the group name
     *
     * @return $this
     */
    public function setGroupName(?string $groupName): static
    {
        if (null === $groupName) {
            $this->groupName = null;
            $this->groupNameCanonical = null;
        } else {
            $this->groupName = trim(strip_tags($groupName));
            $this->groupNameCanonical = StringHandler::slugify($this->getGroupName());
        }

        return $this;
    }

    public function getGroupNameCanonical(): ?string
    {
        return $this->groupNameCanonical;
    }

    public function isExpanded(): bool
    {
        return $this->expanded;
    }

    /**
     * @return $this
     */
    public function setExpanded(bool $expanded): static
    {
        $this->expanded = $expanded;

        return $this;
    }
}
