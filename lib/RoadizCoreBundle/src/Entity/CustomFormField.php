<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use RZ\Roadiz\Core\AbstractEntities\AbstractField;

/**
 * CustomFormField entities are used to create CustomForms with
 * custom data structure.
 */
#[
    ORM\Entity(repositoryClass: "RZ\Roadiz\CoreBundle\Repository\CustomFormFieldRepository"),
    ORM\Table(name: "custom_form_fields"),
    ORM\UniqueConstraint(columns: ["name", "custom_form_id"]),
    ORM\Index(columns: ["position"]),
    ORM\Index(columns: ["group_name"]),
    ORM\Index(columns: ["type"]),
    ORM\Index(columns: ["custom_form_id", "position"], name: "cfield_customform_position"),
    ORM\HasLifecycleCallbacks,
    UniqueEntity(fields: ["label", "customForm"])
]
class CustomFormField extends AbstractField
{
    /**
     * @inheritdoc
     */
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
        AbstractField::COUNTRY_T => 'country.type',
        AbstractField::DOCUMENTS_T => 'documents.type',
    ];

    #[
        ORM\ManyToOne(targetEntity: CustomForm::class, inversedBy: "fields"),
        ORM\JoinColumn(name: "custom_form_id", referencedColumnName: "id", onDelete: "CASCADE"),
        Serializer\Exclude,
        SymfonySerializer\Ignore
    ]
    private ?CustomForm $customForm = null;

    /**
     * @var Collection<int, CustomFormFieldAttribute>
     */
    #[
        ORM\OneToMany(mappedBy: "customFormField", targetEntity: CustomFormFieldAttribute::class),
        Serializer\Exclude,
        SymfonySerializer\Ignore
    ]
    private Collection $customFormFieldAttributes;

    #[
        ORM\Column(name: "field_required", type: "boolean", nullable: false, options: ["default" => false]),
        Serializer\Groups(["custom_form"]),
        SymfonySerializer\Groups(["custom_form"])
    ]
    private bool $required = false;

    public function __construct()
    {
        parent::__construct();
        $this->customFormFieldAttributes = new ArrayCollection();
    }

    /**
     * @param string $label
     *
     * @return $this
     */
    public function setLabel($label)
    {
        parent::setLabel($label);
        $this->setName($label);

        return $this;
    }

    /**
     * @return CustomForm|null
     */
    public function getCustomForm(): ?CustomForm
    {
        return $this->customForm;
    }

    /**
     * @param CustomForm|null $customForm
     *
     * @return $this
     */
    public function setCustomForm(CustomForm $customForm = null): CustomFormField
    {
        $this->customForm = $customForm;
        if (null !== $customForm) {
            $this->customForm->addField($this);
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getCustomFormFieldAttribute(): Collection
    {
        return $this->customFormFieldAttributes;
    }

    /**
     * @return bool $isRequired
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * @param bool $required
     *
     * @return $this
     */
    public function setRequired(bool $required): CustomFormField
    {
        $this->required = $required;
        return $this;
    }

    /**
     * @return string
     */
    public function getOneLineSummary(): string
    {
        return $this->getId() . " — " . $this->getName() . " — " . $this->getLabel() . PHP_EOL;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->getId();
    }

    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
            $this->customForm = null;
            $this->customFormFieldAttributes = new ArrayCollection();
        }
    }
}
