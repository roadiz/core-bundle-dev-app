<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use RZ\Roadiz\CoreBundle\Repository\CustomFormAnswerRepository;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use RZ\Roadiz\Core\AbstractEntities\AbstractEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[
    ORM\Entity(repositoryClass: CustomFormAnswerRepository::class),
    ORM\Table(name: "custom_form_answers"),
    ORM\Index(columns: ["ip"]),
    ORM\Index(columns: ["submitted_at"]),
    ORM\Index(columns: ["custom_form_id", "submitted_at"], name: "answer_customform_submitted_at")
]
class CustomFormAnswer extends AbstractEntity
{
    #[
        ORM\Column(name: "ip", type: "string", length: 46, nullable: false),
        Serializer\Groups(["custom_form_answer"]),
        SymfonySerializer\Groups(["custom_form_answer"]),
        Assert\Length(max: 46)
    ]
    private string $ip = '';

    #[
        ORM\Column(name: "submitted_at", type: "datetime", nullable: false),
        Serializer\Groups(["custom_form_answer"]),
        SymfonySerializer\Groups(["custom_form_answer"])
    ]
    private \DateTime $submittedAt;

    /**
     * @var Collection<int, CustomFormFieldAttribute>
     */
    #[
        ORM\OneToMany(
            mappedBy: "customFormAnswer",
            targetEntity: CustomFormFieldAttribute::class,
            cascade: ["ALL"]
        ),
        Serializer\Groups(["custom_form_answer"]),
        SymfonySerializer\Groups(["custom_form_answer"])
    ]
    private Collection $answerFields;

    #[
        ORM\ManyToOne(
            targetEntity: CustomForm::class,
            inversedBy: "customFormAnswers"
        ),
        ORM\JoinColumn(name: "custom_form_id", referencedColumnName: "id", nullable: false, onDelete: "CASCADE"),
        Serializer\Exclude,
        SymfonySerializer\Ignore
    ]
    private CustomForm $customForm;

    public function __construct()
    {
        $this->answerFields = new ArrayCollection();
        $this->submittedAt = new \DateTime();
    }

    /**
     * @param CustomFormFieldAttribute $field
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
     * @param CustomFormFieldAttribute $field
     *
     * @return $this
     */
    public function removeAnswerField(CustomFormFieldAttribute $field): CustomFormAnswer
    {
        if ($this->getAnswerFields()->contains($field)) {
            $this->getAnswerFields()->removeElement($field);
        }

        return $this;
    }

    /**
     * @return CustomForm
     */
    public function getCustomForm(): CustomForm
    {
        return $this->customForm;
    }

    /**
     * @param CustomForm $customForm
     * @return $this
     */
    public function setCustomForm(CustomForm $customForm): CustomFormAnswer
    {
        $this->customForm = $customForm;
        return $this;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->getId();
    }

    /**
     * @return string
     */
    public function getIp(): string
    {
        return $this->ip;
    }

    /**
     * @param string $ip
     *
     * @return $this
     */
    public function setIp(string $ip): CustomFormAnswer
    {
        $this->ip = $ip;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getSubmittedAt(): ?\DateTime
    {
        return $this->submittedAt;
    }

    /**
     * @param \DateTime $submittedAt
     *
     * @return $this
     */
    public function setSubmittedAt(\DateTime $submittedAt): CustomFormAnswer
    {
        $this->submittedAt = $submittedAt;
        return $this;
    }

    /**
     * @return string|null
     * @throws \Exception
     */
    public function getEmail(): ?string
    {
        $attribute = $this->getAnswerFields()->filter(function (CustomFormFieldAttribute $attribute) {
            return $attribute->getCustomFormField()->isEmail();
        })->first();
        return $attribute ? (string) $attribute->getValue() : null;
    }

    /**
     * @param bool $namesAsKeys Use fields name as key. Default: true
     * @return array
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
