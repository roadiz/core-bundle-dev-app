<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\Core\AbstractEntities\SequentialIdTrait;
use RZ\Roadiz\CoreBundle\Repository\CustomFormAnswerRepository;
use Symfony\Component\Serializer\Attribute as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

#[
    ORM\Entity(repositoryClass: CustomFormAnswerRepository::class),
    ORM\Table(name: 'custom_form_answers'),
    ORM\Index(columns: ['ip']),
    ORM\Index(columns: ['submitted_at']),
    ORM\Index(columns: ['custom_form_id', 'submitted_at'], name: 'answer_customform_submitted_at')
]
class CustomFormAnswer implements \Stringable, PersistableInterface
{
    use SequentialIdTrait;

    #[
        ORM\Column(name: 'ip', type: 'string', length: 46, nullable: false),
        SymfonySerializer\Groups(['custom_form_answer']),
        Assert\Length(max: 46)
    ]
    private string $ip = '';

    #[
        ORM\Column(name: 'submitted_at', type: 'datetime', nullable: false),
        SymfonySerializer\Groups(['custom_form_answer'])
    ]
    private \DateTime $submittedAt;

    /**
     * @var Collection<int, CustomFormFieldAttribute>
     */
    #[
        ORM\OneToMany(
            mappedBy: 'customFormAnswer',
            targetEntity: CustomFormFieldAttribute::class,
            cascade: ['ALL'],
            orphanRemoval: true
        ),
        SymfonySerializer\Groups(['custom_form_answer'])
    ]
    private Collection $answerFields;

    #[
        ORM\ManyToOne(
            targetEntity: CustomForm::class,
            inversedBy: 'customFormAnswers'
        ),
        ORM\JoinColumn(name: 'custom_form_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE'),
        SymfonySerializer\Ignore
    ]
    private CustomForm $customForm;

    public function __construct()
    {
        $this->answerFields = new ArrayCollection();
        $this->submittedAt = new \DateTime();
    }

    /**
     * @return $this
     */
    public function addAnswerField(CustomFormFieldAttribute $field): CustomFormAnswer
    {
        if (!$this->getAnswerFields()->contains($field)) {
            $this->getAnswerFields()->add($field);
        }

        return $this;
    }

    /**
     * @return Collection<int, CustomFormFieldAttribute>
     */
    public function getAnswerFields(): Collection
    {
        return $this->answerFields;
    }

    /**
     * @return $this
     */
    public function removeAnswerField(CustomFormFieldAttribute $field): CustomFormAnswer
    {
        if ($this->getAnswerFields()->contains($field)) {
            $this->getAnswerFields()->removeElement($field);
        }

        return $this;
    }

    public function getCustomForm(): CustomForm
    {
        return $this->customForm;
    }

    /**
     * @return $this
     */
    public function setCustomForm(CustomForm $customForm): CustomFormAnswer
    {
        $this->customForm = $customForm;

        return $this;
    }

    #[\Override]
    public function __toString(): string
    {
        return (string) $this->getId();
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    /**
     * @return $this
     */
    public function setIp(string $ip): CustomFormAnswer
    {
        $this->ip = $ip;

        return $this;
    }

    public function getSubmittedAt(): ?\DateTime
    {
        return $this->submittedAt;
    }

    /**
     * @return $this
     */
    public function setSubmittedAt(\DateTime $submittedAt): CustomFormAnswer
    {
        $this->submittedAt = $submittedAt;

        return $this;
    }

    public function getEmail(): ?string
    {
        $attribute = $this->getAnswerFields()
            ->filter(fn (CustomFormFieldAttribute $attribute) => $attribute->getCustomFormField()->isEmail())
            ->first();

        if (!$attribute instanceof CustomFormFieldAttribute) {
            return null;
        }

        $email = $attribute->getValue();

        if (null === $email || false === filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        return $email;
    }

    /**
     * @param bool $namesAsKeys Use fields name as key. Default: true
     *
     * @throws \Exception
     */
    public function toArray(bool $namesAsKeys = true): array
    {
        $answers = [];
        /** @var CustomFormFieldAttribute $answer */
        foreach ($this->answerFields as $answer) {
            $field = $answer->getCustomFormField();
            if ($namesAsKeys) {
                $answers[$field->getName()] = $answer->getValue();
            } else {
                $answers[] = [
                    'name' => $field->getName(),
                    'label' => $field->getLabel(),
                    'description' => $field->getDescription(),
                    'value' => $answer->getValue(),
                ];
            }
        }

        return $answers;
    }
}
