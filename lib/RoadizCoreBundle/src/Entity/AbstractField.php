<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use RZ\Roadiz\Core\AbstractEntities\AbstractPositioned;
use RZ\Roadiz\CoreBundle\Enum\FieldType;
use RZ\Roadiz\Utils\StringHandler;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Yaml\Yaml;

#[ORM\MappedSuperclass,
    ORM\Table,
    ORM\Index(columns: ['position']),
    ORM\Index(columns: ['group_name']),
    ORM\Index(columns: ['group_name_canonical']),
    Serializer\ExclusionPolicy('all')]
abstract class AbstractField extends AbstractPositioned
{
    use FieldTypeTrait;

    /**
     * String field is a simple 255 characters long text.
     *
     * @deprecated Use FieldType enum instead
     */
    public const STRING_T = 0;
    /**
     * DateTime field is a combined Date and Time.
     *
     * @see \DateTime
     * @deprecated Use FieldType enum instead
     */
    public const DATETIME_T = 1;
    /**
     * Text field is 65000 characters long text.
     *
     * @deprecated Use FieldType enum instead
     */
    public const TEXT_T = 2;
    /**
     * Rich-text field is an HTML text using a WYSIWYG editor.
     *
     * Use Markdown type instead. WYSIWYG is evil.
     *
     * @deprecated Use FieldType enum instead
     */
    public const RICHTEXT_T = 3;
    /**
     * Markdown field is a pseudo-coded text which is render
     * with a simple editor.
     *
     * @deprecated Use FieldType enum instead
     */
    public const MARKDOWN_T = 4;
    /**
     * Boolean field is a simple switch between 0 and 1.
     *
     * @deprecated Use FieldType enum instead
     */
    public const BOOLEAN_T = 5;
    /**
     * Integer field is a non-floating number.
     *
     * @deprecated Use FieldType enum instead
     */
    public const INTEGER_T = 6;
    /**
     * Decimal field is a floating number.
     *
     * @deprecated Use FieldType enum instead
     */
    public const DECIMAL_T = 7;
    /**
     * Email field is a short text which must
     * comply with email rules.
     *
     * @deprecated Use FieldType enum instead
     */
    public const EMAIL_T = 8;
    /**
     * Documents field helps to link NodesSources with Documents.
     *
     * @deprecated Use FieldType enum instead
     */
    public const DOCUMENTS_T = 9;
    /**
     * Password field is a simple text data rendered
     * as a password input with a confirmation.
     *
     * @deprecated Use FieldType enum instead
     */
    public const PASSWORD_T = 10;
    /**
     * Colour field is a hexadecimal string which is rendered
     * with a colour chooser.
     *
     * @deprecated Use FieldType enum instead
     */
    public const COLOUR_T = 11;
    /**
     * Geotag field is a Map widget which stores
     * a Latitude and Longitude as an array.
     *
     * @deprecated Use FieldType enum instead
     */
    public const GEOTAG_T = 12;
    /**
     * Nodes field helps to link Nodes with other Nodes entities.
     *
     * @deprecated Use FieldType enum instead
     */
    public const NODES_T = 13;
    /**
     * Nodes field helps to link NodesSources with Users entities.
     *
     * @deprecated Use FieldType enum instead
     */
    public const USER_T = 14;
    /**
     * Enum field is a simple select box with default values.
     *
     * @deprecated Use FieldType enum instead
     */
    public const ENUM_T = 15;
    /**
     * Children field is a virtual field, it will only display a
     * NodeTreeWidget to show current Node children.
     *
     * @deprecated Use FieldType enum instead
     */
    public const CHILDREN_T = 16;
    /**
     * Nodes field helps to link Nodes with CustomForms entities.
     *
     * @deprecated Use FieldType enum instead
     */
    public const CUSTOM_FORMS_T = 17;
    /**
     * Multiple field is a simple select box with multiple choices.
     *
     * @deprecated Use FieldType enum instead
     */
    public const MULTIPLE_T = 18;
    /**
     * Radio group field is like ENUM_T but rendered as a radio
     * button group.
     *
     * @deprecated This option does not mean any data type, just presentation
     */
    public const RADIO_GROUP_T = 19;
    /**
     * Check group field is like MULTIPLE_T but rendered as
     * a checkbox group.
     *
     * @deprecated This option does not mean any data type, just presentation
     */
    public const CHECK_GROUP_T = 20;
    /**
     * Multi-Geotag field is a Map widget which stores
     * multiple Latitude and Longitude with names and icon options.
     *
     * @deprecated Use FieldType enum instead
     */
    public const MULTI_GEOTAG_T = 21;
    /**
     * @see \DateTime
     * @deprecated Use FieldType enum instead
     */
    public const DATE_T = 22;
    /**
     * Textarea to write Json syntax code.
     *
     * @deprecated Use FieldType enum instead
     */
    public const JSON_T = 23;
    /**
     * Textarea to write CSS syntax code.
     *
     * @deprecated Use FieldType enum instead
     */
    public const CSS_T = 24;
    /**
     * Select-box to choose ISO Country.
     *
     * @deprecated Use FieldType enum instead
     */
    public const COUNTRY_T = 25;
    /**
     * Textarea to write YAML syntax text.
     *
     * @deprecated Use FieldType enum instead
     */
    public const YAML_T = 26;
    /**
     * «Many to many» join to a custom doctrine entity class.
     *
     * @deprecated Use FieldType enum instead
     */
    public const MANY_TO_MANY_T = 27;
    /**
     * «Many to one» join to a custom doctrine entity class.
     *
     * @deprecated Use FieldType enum instead
     */
    public const MANY_TO_ONE_T = 28;
    /**
     * Array field to reference external objects ID (eg. from an API).
     *
     * @deprecated Use FieldType enum instead
     */
    public const MULTI_PROVIDER_T = 29;
    /**
     * String field to reference an external object ID (eg. from an API).
     *
     * @deprecated Use FieldType enum instead
     */
    public const SINGLE_PROVIDER_T = 30;
    /**
     * Collection field.
     *
     * @deprecated Use FieldType enum instead
     */
    public const COLLECTION_T = 31;

    /**
     * Associates abstract field type to a readable string.
     *
     * These string will be used as translation key.
     *
     * @var array<string>
     *
     * @internal
     *
     * @deprecated Use FieldType enum instead
     */
    #[SymfonySerializer\Ignore]
    public static array $typeToHuman = [
        AbstractField::STRING_T => 'string.type',
        AbstractField::DATETIME_T => 'date-time.type',
        AbstractField::DATE_T => 'date.type',
        AbstractField::TEXT_T => 'text.type',
        AbstractField::MARKDOWN_T => 'markdown.type',
        AbstractField::BOOLEAN_T => 'boolean.type',
        AbstractField::INTEGER_T => 'integer.type',
        AbstractField::DECIMAL_T => 'decimal.type',
        AbstractField::EMAIL_T => 'email.type',
        AbstractField::ENUM_T => 'single-choice.type',
        AbstractField::MULTIPLE_T => 'multiple-choice.type',
        AbstractField::DOCUMENTS_T => 'documents.type',
        AbstractField::NODES_T => 'nodes.type',
        AbstractField::CHILDREN_T => 'children-nodes.type',
        AbstractField::COLOUR_T => 'colour.type',
        AbstractField::GEOTAG_T => 'geographic.coordinates.type',
        AbstractField::CUSTOM_FORMS_T => 'custom-forms.type',
        AbstractField::MULTI_GEOTAG_T => 'multiple.geographic.coordinates.type',
        AbstractField::JSON_T => 'json.type',
        AbstractField::CSS_T => 'css.type',
        AbstractField::COUNTRY_T => 'country.type',
        AbstractField::YAML_T => 'yaml.type',
        AbstractField::MANY_TO_MANY_T => 'many-to-many.type',
        AbstractField::MANY_TO_ONE_T => 'many-to-one.type',
        AbstractField::SINGLE_PROVIDER_T => 'single-provider.type',
        AbstractField::MULTI_PROVIDER_T => 'multiple-provider.type',
        AbstractField::COLLECTION_T => 'collection.type',
    ];
    /**
     * Associates abstract field type to a Doctrine type.
     *
     * @var array<string|null>
     *
     * @internal
     *
     * @deprecated Use FieldType enum instead
     */
    #[SymfonySerializer\Ignore]
    public static array $typeToDoctrine = [
        AbstractField::STRING_T => 'string',
        AbstractField::DATETIME_T => 'datetime',
        AbstractField::DATE_T => 'datetime',
        AbstractField::RICHTEXT_T => 'text',
        AbstractField::TEXT_T => 'text',
        AbstractField::MARKDOWN_T => 'text',
        AbstractField::BOOLEAN_T => 'boolean',
        AbstractField::INTEGER_T => 'integer',
        AbstractField::DECIMAL_T => 'decimal',
        AbstractField::EMAIL_T => 'string',
        AbstractField::ENUM_T => 'string',
        AbstractField::MULTIPLE_T => 'json',
        AbstractField::DOCUMENTS_T => null,
        AbstractField::NODES_T => null,
        AbstractField::CHILDREN_T => null,
        AbstractField::COLOUR_T => 'string',
        AbstractField::GEOTAG_T => 'json',
        AbstractField::CUSTOM_FORMS_T => null,
        AbstractField::MULTI_GEOTAG_T => 'json',
        AbstractField::JSON_T => 'text',
        AbstractField::CSS_T => 'text',
        AbstractField::COUNTRY_T => 'string',
        AbstractField::YAML_T => 'text',
        AbstractField::MANY_TO_MANY_T => null,
        AbstractField::MANY_TO_ONE_T => null,
        AbstractField::SINGLE_PROVIDER_T => 'string',
        AbstractField::MULTI_PROVIDER_T => 'json',
        AbstractField::COLLECTION_T => 'json',
    ];

    /**
     * List searchable fields types in a searchEngine such as Solr.
     *
     * @var array<int>
     *
     * @internal
     *
     * @deprecated Use FieldType enum instead
     */
    #[SymfonySerializer\Ignore]
    protected static array $searchableTypes = [
        AbstractField::STRING_T,
        AbstractField::RICHTEXT_T,
        AbstractField::TEXT_T,
        AbstractField::MARKDOWN_T,
    ];

    #[ORM\Column(name: 'group_name', type: 'string', length: 250, nullable: true),
        Assert\Length(max: 250),
        SymfonySerializer\Groups(['node_type', 'node_type:import', 'setting']),
        Serializer\Groups(['node_type', 'setting']),
        Serializer\Type('string'),
        Serializer\Expose]
    protected ?string $groupName = null;

    #[ORM\Column(name: 'group_name_canonical', type: 'string', length: 250, nullable: true),
        Serializer\Groups(['node_type', 'setting']),
        SymfonySerializer\Groups(['node_type', 'setting']),
        Assert\Length(max: 250),
        Serializer\Type('string'),
        Serializer\Expose]
    protected ?string $groupNameCanonical = null;

    #[ORM\Column(type: 'string', length: 250),
        Serializer\Expose,
        Serializer\Groups(['node_type', 'setting']),
        SymfonySerializer\Groups(['node_type', 'node_type:import', 'setting']),
        Assert\Length(max: 250),
        Serializer\Type('string'),
        Assert\NotBlank(),
        Assert\NotNull()]
    protected string $name;

    #[ORM\Column(type: 'string', length: 250),
        Serializer\Expose,
        Serializer\Groups(['node_type', 'setting']),
        Serializer\Type('string'),
        SymfonySerializer\Groups(['node_type', 'node_type:import', 'setting']),
        Assert\Length(max: 250),
        Assert\NotBlank(),
        Assert\NotNull()]
    protected ?string $label;

    #[ORM\Column(type: 'string', length: 250, nullable: true),
        Serializer\Expose,
        Serializer\Groups(['node_type', 'setting']),
        SymfonySerializer\Groups(['node_type', 'node_type:import', 'setting']),
        Assert\Length(max: 250),
        Serializer\Type('string')]
    protected ?string $placeholder = null;

    #[ORM\Column(type: 'text', nullable: true),
        Serializer\Expose,
        Serializer\Groups(['node_type', 'setting']),
        SymfonySerializer\Groups(['node_type', 'node_type:import', 'setting']),
        Serializer\Type('string')]
    protected ?string $description = null;

    #[ORM\Column(name: 'default_values', type: 'text', nullable: true),
        Serializer\Groups(['node_type', 'setting']),
        SymfonySerializer\Groups(['node_type', 'setting']),
        Serializer\Type('string'),
        Serializer\Expose]
    protected ?string $defaultValues = null;

    #[ORM\Column(
        type: Types::SMALLINT,
        nullable: false,
        enumType: FieldType::class,
        options: ['default' => FieldType::STRING_T]
    ),
        Serializer\Groups(['node_type', 'setting']),
        SymfonySerializer\Groups(['node_type', 'setting']),
        Serializer\Type('int'),
        Serializer\Expose]
    protected FieldType $type = FieldType::STRING_T;

    /**
     * If current field data should be expanded (for choices and country types).
     */
    #[ORM\Column(name: 'expanded', type: 'boolean', nullable: false, options: ['default' => false]),
        Serializer\Groups(['node_type', 'setting']),
        SymfonySerializer\Groups(['node_type', 'node_type:import', 'setting']),
        Serializer\Type('bool'),
        Serializer\Expose]
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
    public function setName(?string $name): AbstractField
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
    public function setLabel(?string $label): AbstractField
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
    public function setPlaceholder(?string $placeholder): AbstractField
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
    public function setDescription(?string $description): AbstractField
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
    public function setDefaultValues(?string $defaultValues): AbstractField
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
    public function setGroupName(?string $groupName): AbstractField
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
    public function setExpanded(bool $expanded): AbstractField
    {
        $this->expanded = $expanded;

        return $this;
    }
}
