<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use ApiPlatform\Metadata\ApiFilter;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use RZ\Roadiz\CoreBundle\Repository\CustomFormRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use RZ\Roadiz\Core\AbstractEntities\AbstractDateTimed;
use RZ\Roadiz\Utils\StringHandler;
use RZ\Roadiz\CoreBundle\Api\Filter as RoadizFilter;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * CustomForms describe each node structure family,
 * They are mandatory before creating any Node.
 */
#[
    ORM\Entity(repositoryClass: CustomFormRepository::class),
    ORM\Table(name: "custom_forms"),
    ORM\HasLifecycleCallbacks,
    UniqueEntity(fields: ["name"]),
    ORM\Index(columns: ["created_at"], name: "custom_form_created_at"),
    ORM\Index(columns: ["updated_at"], name: "custom_form_updated_at"),
]
class CustomForm extends AbstractDateTimed
{
    #[
        ORM\Column(name: "color", type: "string", length: 7, unique: false, nullable: true),
        Serializer\Groups(["custom_form", "nodes_sources"]),
        Assert\Length(max: 7),
        SymfonySerializer\Ignore()
    ]
    protected ?string $color = '#000000';

    #[
        ORM\Column(type: "string", length: 250, unique: true),
        Serializer\Groups(["custom_form", "nodes_sources"]),
        SymfonySerializer\Groups(["custom_form", "nodes_sources"]),
        Assert\NotNull(),
        Assert\NotBlank(),
        Assert\Length(max: 250),
        SymfonySerializer\Ignore()
    ]
    private string $name = 'Untitled';

    #[
        ORM\Column(name: "display_name", type: "string", length: 250),
        Serializer\Groups(["custom_form", "nodes_sources"]),
        SymfonySerializer\Groups(["custom_form", "nodes_sources"]),
        Assert\NotNull(),
        Assert\NotBlank(),
        Assert\Length(max: 250),
        SymfonySerializer\Ignore()
    ]
    private string $displayName = 'Untitled';

    #[
        ORM\Column(type: "text", nullable: true),
        Serializer\Groups(["custom_form", "nodes_sources"]),
        SymfonySerializer\Groups(["custom_form", "nodes_sources"]),
        SymfonySerializer\Ignore()
    ]
    private ?string $description = null;

    #[
        ORM\Column(type: "text", nullable: true),
        Serializer\Groups(["custom_form"]),
        SymfonySerializer\Groups(["custom_form"]),
        SymfonySerializer\Ignore()
    ]
    private ?string $email = null;

    #[
        ORM\Column(type: "string", length: 15, nullable: true),
        Serializer\Groups(["custom_form"]),
        SymfonySerializer\Groups(["custom_form"]),
        Assert\Length(max: 15),
        SymfonySerializer\Ignore()
    ]
    private ?string $retentionTime = null;

    #[
        ORM\Column(type: "boolean", nullable: false, options: ["default" => true]),
        Serializer\Groups(["custom_form", "nodes_sources"]),
        SymfonySerializer\Groups(["custom_form", "nodes_sources"]),
        SymfonySerializer\Ignore()
    ]
    private bool $open = true;

    #[
        ApiFilter(RoadizFilter\ArchiveFilter::class),
        ORM\Column(name: "close_date", type: "datetime", nullable: true),
        Serializer\Groups(["custom_form", "nodes_sources"]),
        SymfonySerializer\Groups(["custom_form", "nodes_sources"]),
        SymfonySerializer\Ignore()
    ]
    private ?DateTime $closeDate = null;

    /**
     * @var Collection<int, CustomFormField>
     */
    #[
        ORM\OneToMany(
            mappedBy: "customForm",
            targetEntity: CustomFormField::class,
            cascade: ["ALL"],
            orphanRemoval: true
        ),
        ORM\OrderBy(["position" => "ASC"]),
        Serializer\Groups(["custom_form"]),
        SymfonySerializer\Groups(["custom_form"]),
        SymfonySerializer\Ignore()
    ]
    private Collection $fields;

    /**
     * @var Collection<int, CustomFormAnswer>
     */
    #[
        ORM\OneToMany(
            mappedBy: "customForm",
            targetEntity: CustomFormAnswer::class,
            cascade: ["ALL"],
            orphanRemoval: true
        ),
        Serializer\Exclude,
        SymfonySerializer\Ignore
    ]
    private Collection $customFormAnswers;

    /**
     * @var Collection<int, NodesCustomForms>
     */
    #[
        ORM\OneToMany(mappedBy: "customForm", targetEntity: NodesCustomForms::class, fetch: "EXTRA_LAZY"),
        Serializer\Exclude,
        SymfonySerializer\Ignore
    ]
    private Collection $nodes;

    public function __construct()
    {
        $this->fields = new ArrayCollection();
        $this->customFormAnswers = new ArrayCollection();
        $this->nodes = new ArrayCollection();
        $this->initAbstractDateTimed();
    }

    /**
     * @return string
     */
    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    /**
     * @param string|null $displayName
     * @return $this
     */
    public function setDisplayName(?string $displayName): CustomForm
    {
        $this->displayName = $displayName ?? '';
        $this->setName($displayName ?? '');

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return $this
     */
    public function setDescription(?string $description): CustomForm
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     *
     * @return $this
     */
    public function setEmail(?string $email): CustomForm
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @param bool $open
     *
     * @return $this
     */
    public function setOpen(bool $open): CustomForm
    {
        $this->open = $open;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getCloseDate(): ?\DateTime
    {
        return $this->closeDate;
    }

    /**
     * @param \DateTime|null $closeDate
     *
     * @return $this
     */
    public function setCloseDate(?\DateTime $closeDate): CustomForm
    {
        $this->closeDate = $closeDate;
        return $this;
    }

    /**
     * Combine open flag and closeDate to determine
     * if current form is still available.
     *
     * @return bool
     */
    #[
        Serializer\Groups(["custom_form", "nodes_sources"]),
        Serializer\VirtualProperty,
        SymfonySerializer\Ignore
    ]
    public function isFormStillOpen(): bool
    {
        return (null === $this->getCloseDate() || $this->getCloseDate() >= (new \DateTime('now'))) &&
            $this->open === true;
    }

    /**
     * Gets the value of color.
     *
     * @return string
     */
    public function getColor(): string
    {
        return $this->color;
    }

    /**
     * @return $this
     */
    public function setColor(?string $color): CustomForm
    {
        $this->color = $color;
        return $this;
    }

    /**
     * Get every node-type fields names in
     * a simple array.
     *
     * @return array
     */
    public function getFieldsNames(): array
    {
        $namesArray = [];

        foreach ($this->getFields() as $field) {
            $namesArray[] = $field->getName();
        }

        return $namesArray;
    }

    /**
     * @return Collection
     */
    public function getFields(): Collection
    {
        return $this->fields;
    }

    /**
     * Get every node-type fields names in
     * a simple array.
     *
     * @return array
     */
    public function getFieldsLabels(): array
    {
        $namesArray = [];

        foreach ($this->getFields() as $field) {
            $namesArray[] = $field->getLabel();
        }

        return $namesArray;
    }

    /**
     * @param CustomFormField $field
     * @return CustomForm
     */
    public function addField(CustomFormField $field): CustomForm
    {
        if (!$this->getFields()->contains($field)) {
            $this->getFields()->add($field);
            $field->setCustomForm($this);
        }

        return $this;
    }

    /**
     * @param CustomFormField $field
     * @return CustomForm
     */
    public function removeField(CustomFormField $field): CustomForm
    {
        if ($this->getFields()->contains($field)) {
            $this->getFields()->removeElement($field);
        }

        return $this;
    }

    /**
     * @return Collection<int, CustomFormAnswer>
     */
    public function getCustomFormAnswers(): Collection
    {
        return $this->customFormAnswers;
    }

    /**
     * @return string
     */
    public function getOneLineSummary(): string
    {
        return $this->getId() . " — " . $this->getName() .
            " — Open : " . ($this->isOpen() ? 'true' : 'false') . PHP_EOL;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name): CustomForm
    {
        $this->name = StringHandler::slugify($name);
        return $this;
    }

    /**
     * @return bool
     */
    public function isOpen(): bool
    {
        return $this->open;
    }

    /**
     * @return string|null
     */
    public function getRetentionTime(): ?string
    {
        return $this->retentionTime;
    }

    public function getRetentionTimeInterval(): ?\DateInterval
    {
        try {
            return null !== $this->getRetentionTime() ? new \DateInterval($this->getRetentionTime()) : null;
        } catch (\Exception $exception) {
            return null;
        }
    }

    /**
     * @param string|null $retentionTime
     * @return CustomForm
     */
    public function setRetentionTime(?string $retentionTime): CustomForm
    {
        $this->retentionTime = $retentionTime;
        return $this;
    }

    /**
     * @return string $text
     */
    public function getFieldsSummary(): string
    {
        $text = "|" . PHP_EOL;
        foreach ($this->getFields() as $field) {
            $text .= "|--- " . $field->getOneLineSummary();
        }

        return $text;
    }

    /**
     * @return Collection
     */
    public function getNodes(): Collection
    {
        return $this->nodes;
    }

    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
            $suffix = "-" . uniqid();
            $this->name .= $suffix;
            $this->displayName .= $suffix;
            $this->customFormAnswers = new ArrayCollection();
            $fields = $this->getFields();
            $this->fields = new ArrayCollection();
            /** @var CustomFormField $field */
            foreach ($fields as $field) {
                $cloneField = clone $field;
                $this->fields->add($cloneField);
                $cloneField->setCustomForm($this);
            }
            $this->nodes = new ArrayCollection();
            $this->setCreatedAt(new \DateTime());
            $this->setUpdatedAt(new \DateTime());
        }
    }
}
