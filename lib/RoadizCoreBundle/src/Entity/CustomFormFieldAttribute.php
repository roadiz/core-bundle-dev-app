<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\Core\AbstractEntities\AbstractEntity;
use RZ\Roadiz\CoreBundle\Repository\CustomFormFieldAttributeRepository;

/**
 * CustomFormField entities are used to create CustomForms with
 * custom data structure.
 */
#[
    ORM\Entity(repositoryClass: CustomFormFieldAttributeRepository::class),
    ORM\Table(name: "custom_form_field_attributes"),
    ORM\Index(columns: ["custom_form_answer_id", "custom_form_field_id"], name: "cffattribute_answer_field"),
    ORM\HasLifecycleCallbacks
]
class CustomFormFieldAttribute extends AbstractEntity
{
    #[
        ORM\ManyToOne(targetEntity: CustomFormAnswer::class, inversedBy: "answerFields"),
        ORM\JoinColumn(name: "custom_form_answer_id", referencedColumnName: "id", onDelete: "CASCADE")
    ]
    protected ?CustomFormAnswer $customFormAnswer = null;

    #[
        ORM\ManyToOne(targetEntity: CustomFormField::class, inversedBy: "customFormFieldAttributes"),
        ORM\JoinColumn(name: "custom_form_field_id", referencedColumnName: "id", onDelete: "CASCADE")
    ]
    protected ?CustomFormField $customFormField = null;

    /**
     * @var Collection<int, Document>
     */
    #[
        ORM\ManyToMany(targetEntity: "RZ\Roadiz\CoreBundle\Entity\Document", inversedBy: "customFormFieldAttributes"),
        ORM\JoinTable(name: "custom_form_answers_documents"),
        ORM\JoinColumn(name: "customformfieldattribute_id", onDelete: "CASCADE"),
        ORM\InverseJoinColumn(name: "document_id", onDelete: "CASCADE")
    ]
    protected Collection $documents;

    #[ORM\Column(type: "text", nullable: true)]
    protected ?string $value = null;

    public function __construct()
    {
        $this->documents = new ArrayCollection();
    }

    /**
     * @return string|null $value
     * @throws \Exception
     */
    public function getValue(): ?string
    {
        if ($this->getCustomFormField()->isDocuments()) {
            return implode(', ', $this->getDocuments()->map(function (Document $document) {
                return $document->getRelativePath();
            })->toArray());
        }
        if ($this->getCustomFormField()->isDate()) {
            return (new \DateTime($this->value))->format('Y-m-d');
        }
        if ($this->getCustomFormField()->isDateTime()) {
            return (new \DateTime($this->value))->format('Y-m-d H:i:s');
        }
        return $this->value;
    }

    /**
     * @param string|null $value
     * @return $this
     */
    public function setValue(?string $value): CustomFormFieldAttribute
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Gets the value of customFormAnswer.
     *
     * @return CustomFormAnswer|null
     */
    public function getCustomFormAnswer(): ?CustomFormAnswer
    {
        return $this->customFormAnswer;
    }

    /**
     * Sets the value of customFormAnswer.
     *
     * @param CustomFormAnswer $customFormAnswer the custom form answer
     *
     * @return self
     */
    public function setCustomFormAnswer(CustomFormAnswer $customFormAnswer): CustomFormFieldAttribute
    {
        $this->customFormAnswer = $customFormAnswer;

        return $this;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function __toString(): string
    {
        return $this->getValue() ?? '';
    }

    /**
     * @return CustomFormField|null
     */
    public function getCustomFormField(): ?CustomFormField
    {
        return $this->customFormField;
    }

    /**
     * Sets the value of customFormField.
     *
     * @param CustomFormField $customFormField the custom form field
     * @return self
     */
    public function setCustomFormField(CustomFormField $customFormField): CustomFormFieldAttribute
    {
        $this->customFormField = $customFormField;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    /**
     * @param Collection $documents
     *
     * @return CustomFormFieldAttribute
     */
    public function setDocuments(Collection $documents): CustomFormFieldAttribute
    {
        $this->documents = $documents;

        return $this;
    }
}
