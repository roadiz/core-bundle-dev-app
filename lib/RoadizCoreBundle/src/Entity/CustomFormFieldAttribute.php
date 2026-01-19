<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\Core\AbstractEntities\SequentialIdTrait;
use RZ\Roadiz\CoreBundle\Repository\CustomFormFieldAttributeRepository;

/**
 * CustomFormField entities are used to create CustomForms with
 * custom data structure.
 */
#[
    ORM\Entity(repositoryClass: CustomFormFieldAttributeRepository::class),
    ORM\Table(name: 'custom_form_field_attributes'),
    ORM\Index(columns: ['custom_form_answer_id', 'custom_form_field_id'], name: 'cffattribute_answer_field'),
    ORM\HasLifecycleCallbacks
]
class CustomFormFieldAttribute implements \Stringable, PersistableInterface
{
    use SequentialIdTrait;

    #[
        ORM\ManyToOne(targetEntity: CustomFormAnswer::class, inversedBy: 'answerFields'),
        ORM\JoinColumn(name: 'custom_form_answer_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')
    ]
    protected CustomFormAnswer $customFormAnswer;

    #[
        ORM\ManyToOne(targetEntity: CustomFormField::class, inversedBy: 'customFormFieldAttributes'),
        ORM\JoinColumn(name: 'custom_form_field_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')
    ]
    protected CustomFormField $customFormField;

    /**
     * @var Collection<int, Document>
     */
    #[
        ORM\ManyToMany(targetEntity: Document::class, inversedBy: 'customFormFieldAttributes'),
        ORM\JoinTable(name: 'custom_form_answers_documents'),
        ORM\JoinColumn(name: 'customformfieldattribute_id', onDelete: 'CASCADE'),
        ORM\InverseJoinColumn(name: 'document_id', onDelete: 'CASCADE')
    ]
    protected Collection $documents;

    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $value = null;

    public function __construct()
    {
        $this->documents = new ArrayCollection();
    }

    /**
     * @return string|null $value
     *
     * @throws \Exception
     */
    public function getValue(): ?string
    {
        if (null === $this->value) {
            return null;
        }

        if ($this->getCustomFormField()->isDocuments()) {
            return implode(', ', $this->getDocuments()->map(fn (Document $document) => $document->getRelativePath())->toArray());
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
     * @return $this
     */
    public function setValue(?string $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getCustomFormAnswer(): CustomFormAnswer
    {
        return $this->customFormAnswer;
    }

    public function setCustomFormAnswer(CustomFormAnswer $customFormAnswer): CustomFormFieldAttribute
    {
        $this->customFormAnswer = $customFormAnswer;

        return $this;
    }

    /**
     * @throws \Exception
     */
    #[\Override]
    public function __toString(): string
    {
        return $this->getValue() ?? '';
    }

    public function getCustomFormField(): CustomFormField
    {
        return $this->customFormField;
    }

    public function setCustomFormField(CustomFormField $customFormField): CustomFormFieldAttribute
    {
        $this->customFormField = $customFormField;

        return $this;
    }

    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function setDocuments(Collection $documents): CustomFormFieldAttribute
    {
        $this->documents = $documents;

        return $this;
    }
}
