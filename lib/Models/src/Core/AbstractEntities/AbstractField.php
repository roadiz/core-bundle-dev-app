<?php

declare(strict_types=1);

namespace RZ\Roadiz\Core\AbstractEntities;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use RZ\Roadiz\Utils\StringHandler;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

#[
    ORM\MappedSuperclass,
    ORM\Table,
    ORM\Index(columns: ['position']),
    ORM\Index(columns: ['group_name']),
    ORM\Index(columns: ['group_name_canonical']),
    Serializer\ExclusionPolicy('all')
]
abstract class AbstractField extends AbstractPositioned
{
    /**
     * String field is a simple 255 characters long text.
     */
    public const STRING_T = 0;
    /**
     * DateTime field is a combined Date and Time.
     *
     * @see \DateTime
     */
    public const DATETIME_T = 1;
    /**
     * Text field is 65000 characters long text.
     */
    public const TEXT_T = 2;
    /**
     * Rich-text field is an HTML text using a WYSIWYG editor.
     *
     * Use Markdown type instead. WYSIWYG is evil.
     */
    public const RICHTEXT_T = 3;
    /**
     * Markdown field is a pseudo-coded text which is render
     * with a simple editor.
     */
    public const MARKDOWN_T = 4;
    /**
     * Boolean field is a simple switch between 0 and 1.
     */
    public const BOOLEAN_T = 5;
    /**
     * Integer field is a non-floating number.
     */
    public const INTEGER_T = 6;
    /**
     * Decimal field is a floating number.
     */
    public const DECIMAL_T = 7;
    /**
     * Email field is a short text which must
     * comply with email rules.
     */
    public const EMAIL_T = 8;
    /**
     * Documents field helps to link NodesSources with Documents.
     */
    public const DOCUMENTS_T = 9;
    /**
     * Password field is a simple text data rendered
     * as a password input with a confirmation.
     */
    public const PASSWORD_T = 10;
    /**
     * Colour field is a hexadecimal string which is rendered
     * with a colour chooser.
     */
    public const COLOUR_T = 11;
    /**
     * Geotag field is a Map widget which stores
     * a Latitude and Longitude as an array.
     */
    public const GEOTAG_T = 12;
    /**
     * Nodes field helps to link Nodes with other Nodes entities.
     */
    public const NODES_T = 13;
    /**
     * Nodes field helps to link NodesSources with Users entities.
     */
    public const USER_T = 14;
    /**
     * Enum field is a simple select box with default values.
     */
    public const ENUM_T = 15;
    /**
     * Children field is a virtual field, it will only display a
     * NodeTreeWidget to show current Node children.
     */
    public const CHILDREN_T = 16;
    /**
     * Nodes field helps to link Nodes with CustomForms entities.
     */
    public const CUSTOM_FORMS_T = 17;
    /**
     * Multiple field is a simple select box with multiple choices.
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
     */
    public const MULTI_GEOTAG_T = 21;
    /**
     * @see \DateTime
     */
    public const DATE_T = 22;
    /**
     * Textarea to write Json syntax code.
     */
    public const JSON_T = 23;
    /**
     * Textarea to write CSS syntax code.
     */
    public const CSS_T = 24;
    /**
     * Select-box to choose ISO Country.
     */
    public const COUNTRY_T = 25;
    /**
     * Textarea to write YAML syntax text.
     */
    public const YAML_T = 26;
    /**
     * «Many to many» join to a custom doctrine entity class.
     */
    public const MANY_TO_MANY_T = 27;
    /**
     * «Many to one» join to a custom doctrine entity class.
     */
    public const MANY_TO_ONE_T = 28;
    /**
     * Array field to reference external objects ID (eg. from an API).
     */
    public const MULTI_PROVIDER_T = 29;
    /**
     * String field to reference an external object ID (eg. from an API).
     */
    public const SINGLE_PROVIDER_T = 30;
    /**
     * Collection field.
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
     */
    #[SymfonySerializer\Ignore]
    protected static array $searchableTypes = [
        AbstractField::STRING_T,
        AbstractField::RICHTEXT_T,
        AbstractField::TEXT_T,
        AbstractField::MARKDOWN_T,
    ];

    #[
        ORM\Column(name: 'group_name', type: 'string', length: 250, nullable: true),
        Assert\Length(max: 250),
        SymfonySerializer\Groups(['node_type', 'setting']),
        Serializer\Groups(['node_type', 'setting']),
        Serializer\Type('string'),
        Serializer\Expose
    ]
    protected ?string $groupName = null;

    #[
        ORM\Column(name: 'group_name_canonical', type: 'string', length: 250, nullable: true),
        Serializer\Groups(['node_type', 'setting']),
        SymfonySerializer\Groups(['node_type', 'setting']),
        Assert\Length(max: 250),
        Serializer\Type('string'),
        Serializer\Expose
    ]
    protected ?string $groupNameCanonical = null;

    #[
        ORM\Column(type: 'string', length: 250),
        Serializer\Expose,
        Serializer\Groups(['node_type', 'setting']),
        SymfonySerializer\Groups(['node_type', 'setting']),
        Assert\Length(max: 250),
        Serializer\Type('string'),
        Assert\NotBlank(),
        Assert\NotNull()
    ]
    protected string $name;

    #[
        ORM\Column(type: 'string', length: 250),
        Serializer\Expose,
        Serializer\Groups(['node_type', 'setting']),
        Serializer\Type('string'),
        SymfonySerializer\Groups(['node_type', 'setting']),
        Assert\Length(max: 250),
        Assert\NotBlank(),
        Assert\NotNull()
    ]
    protected ?string $label;

    #[
        ORM\Column(type: 'string', length: 250, nullable: true),
        Serializer\Expose,
        Serializer\Groups(['node_type', 'setting']),
        SymfonySerializer\Groups(['node_type', 'setting']),
        Assert\Length(max: 250),
        Serializer\Type('string')
    ]
    protected ?string $placeholder = null;

    #[
        ORM\Column(type: 'text', nullable: true),
        Serializer\Expose,
        Serializer\Groups(['node_type', 'setting']),
        SymfonySerializer\Groups(['node_type', 'setting']),
        Serializer\Type('string')
    ]
    protected ?string $description = null;

    #[
        ORM\Column(name: 'default_values', type: 'text', nullable: true),
        Serializer\Groups(['node_type', 'setting']),
        SymfonySerializer\Groups(['node_type', 'setting']),
        Serializer\Type('string'),
        Serializer\Expose
    ]
    protected ?string $defaultValues = null;

    #[
        ORM\Column(
            type: Types::SMALLINT,
            nullable: false,
            options: ['default' => AbstractField::STRING_T]
        ),
        Serializer\Groups(['node_type', 'setting']),
        SymfonySerializer\Groups(['node_type', 'setting']),
        Serializer\Type('int'),
        Serializer\Expose
    ]
    protected int $type = AbstractField::STRING_T;

    /**
     * If current field data should be expanded (for choices and country types).
     */
    #[
        ORM\Column(name: 'expanded', type: 'boolean', nullable: false, options: ['default' => false]),
        Serializer\Groups(['node_type', 'setting']),
        SymfonySerializer\Groups(['node_type', 'setting']),
        Serializer\Type('bool'),
        Serializer\Expose
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

    public function getTypeName(): string
    {
        if (!key_exists($this->getType(), static::$typeToHuman)) {
            throw new \InvalidArgumentException($this->getType().' cannot be mapped to human label.');
        }

        return static::$typeToHuman[$this->type];
    }

    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @return $this
     */
    public function setType(int $type): AbstractField
    {
        $this->type = $type;

        return $this;
    }

    public function getDoctrineType(): string
    {
        if (!key_exists($this->getType(), static::$typeToDoctrine)) {
            throw new \InvalidArgumentException($this->getType().' cannot be mapped to Doctrine.');
        }

        return static::$typeToDoctrine[$this->getType()] ?? '';
    }

    /**
     * @return bool Is node type field virtual, it's just an association, no doctrine field created
     */
    public function isVirtual(): bool
    {
        return null === static::$typeToDoctrine[$this->getType()];
    }

    /**
     * @return bool Is node type field searchable
     */
    public function isSearchable(): bool
    {
        return in_array($this->getType(), static::$searchableTypes);
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

    public function isString(): bool
    {
        return $this->getType() === static::STRING_T;
    }

    public function isText(): bool
    {
        return $this->getType() === static::TEXT_T;
    }

    public function isDate(): bool
    {
        return $this->getType() === static::DATE_T;
    }

    public function isDateTime(): bool
    {
        return $this->getType() === static::DATETIME_T;
    }

    public function isRichText(): bool
    {
        return $this->getType() === static::RICHTEXT_T;
    }

    public function isMarkdown(): bool
    {
        return $this->getType() === static::MARKDOWN_T;
    }

    public function isBool(): bool
    {
        return $this->isBoolean();
    }

    public function isBoolean(): bool
    {
        return $this->getType() === static::BOOLEAN_T;
    }

    public function isInteger(): bool
    {
        return $this->getType() === static::INTEGER_T;
    }

    public function isDecimal(): bool
    {
        return $this->getType() === static::DECIMAL_T;
    }

    public function isEmail(): bool
    {
        return $this->getType() === static::EMAIL_T;
    }

    public function isDocuments(): bool
    {
        return $this->getType() === static::DOCUMENTS_T;
    }

    public function isPassword(): bool
    {
        return $this->getType() === static::PASSWORD_T;
    }

    public function isColor(): bool
    {
        return $this->isColour();
    }

    public function isColour(): bool
    {
        return $this->getType() === static::COLOUR_T;
    }

    public function isGeoTag(): bool
    {
        return $this->getType() === static::GEOTAG_T;
    }

    public function isNodes(): bool
    {
        return $this->getType() === static::NODES_T;
    }

    public function isUser(): bool
    {
        return $this->getType() === static::USER_T;
    }

    public function isEnum(): bool
    {
        return $this->getType() === static::ENUM_T;
    }

    public function isChildrenNodes(): bool
    {
        return $this->getType() === static::CHILDREN_T;
    }

    public function isCustomForms(): bool
    {
        return $this->getType() === static::CUSTOM_FORMS_T;
    }

    public function isMultiple(): bool
    {
        return $this->getType() === static::MULTIPLE_T;
    }

    public function isMultiGeoTag(): bool
    {
        return $this->getType() === static::MULTI_GEOTAG_T;
    }

    public function isJson(): bool
    {
        return $this->getType() === static::JSON_T;
    }

    public function isYaml(): bool
    {
        return $this->getType() === static::YAML_T;
    }

    public function isCss(): bool
    {
        return $this->getType() === static::CSS_T;
    }

    public function isManyToMany(): bool
    {
        return $this->getType() === static::MANY_TO_MANY_T;
    }

    public function isManyToOne(): bool
    {
        return $this->getType() === static::MANY_TO_ONE_T;
    }

    public function isCountry(): bool
    {
        return $this->getType() === static::COUNTRY_T;
    }

    public function isSingleProvider(): bool
    {
        return $this->getType() === static::SINGLE_PROVIDER_T;
    }

    public function isMultipleProvider(): bool
    {
        return $this->isMultiProvider();
    }

    public function isMultiProvider(): bool
    {
        return $this->getType() === static::MULTI_PROVIDER_T;
    }

    public function isCollection(): bool
    {
        return $this->getType() === static::COLLECTION_T;
    }
}
