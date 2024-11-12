<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;
use Symfony\Component\Validator\Constraints as Assert;

trait FieldAwareEntityTrait
{
    #[ORM\Column(name: 'field_name', length: 50, nullable: false)]
    #[Assert\Length(max: 50)]
    protected string $fieldName;

    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    public function setFieldName(string $fieldName): self
    {
        $this->fieldName = $fieldName;

        return $this;
    }

    /**
     * @deprecated Use setFieldName method instead
     */
    public function setField(NodeTypeFieldInterface $field): self
    {
        $this->fieldName = $field->getName();

        return $this;
    }

    protected function initializeFieldAwareEntityTrait(?NodeTypeFieldInterface $nodeTypeField = null): void
    {
        if (null === $nodeTypeField) {
            return;
        }
        $this->fieldName = $nodeTypeField->getName();
    }
}
