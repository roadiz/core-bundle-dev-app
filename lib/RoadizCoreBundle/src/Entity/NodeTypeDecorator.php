<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\Core\AbstractEntities\AbstractEntity;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Enum\NodeTypeDecoratorProperty;
use RZ\Roadiz\CoreBundle\Repository\NodeTypeDecoratorRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[
    ORM\Entity(repositoryClass: NodeTypeDecoratorRepository::class),
    ORM\Table(name: 'node_type_decorators'),
    UniqueEntity(fields: ['path', 'property']),
    ORM\Index(columns: ['path'], name: 'idx_ntd_path'),
]
class NodeTypeDecorator extends AbstractEntity implements PersistableInterface
{
    final private function __construct(
        #[ORM\Column(type: 'string', length: 255, nullable: false)]
        private string $path,
        #[ORM\Column(type: 'string', nullable: false, enumType: NodeTypeDecoratorProperty::class)]
        private NodeTypeDecoratorProperty $property,
        #[ORM\Column(type: 'string', length: 255, nullable: true)]
        private ?string $value,
    ) {
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public static function withNodeType(NodeType $nodeType, ?NodeTypeField $nodeTypeField, NodeTypeDecoratorProperty $property, ?string $value = null): self
    {
        $path = $nodeType->getName().'.';
        if (null !== $nodeTypeField) {
            if ($property->isNodeTypeProperty()) {
                throw new \LogicException('NodeTypeDecorator::'.$property->name.' property is not a valid nodeType field');
            }
            $path .= $nodeTypeField->getName();
        } else {
            if (!$property->isNodeTypeProperty()) {
                throw new \LogicException('NodeTypeDecorator::'.$property->name.' property requires a nodeType field');
            }
        }

        return new self(
            $path,
            $property,
            $value,
        );
    }

    public function getProperty(): NodeTypeDecoratorProperty
    {
        return $this->property;
    }

    public function setProperty(NodeTypeDecoratorProperty $property): self
    {
        $this->property = $property;

        return $this;
    }

    public function getValue(): int|string|bool|null
    {
        return match ($this->property) {
            NodeTypeDecoratorProperty::NODE_TYPE_COLOR,
            NodeTypeDecoratorProperty::NODE_TYPE_DESCRIPTION,
            NodeTypeDecoratorProperty::NODE_TYPE_DISPLAY_NAME,
            NodeTypeDecoratorProperty::NODE_TYPE_FIELD_LABEL,
            NodeTypeDecoratorProperty::NODE_TYPE_FIELD_DESCRIPTION,
            NodeTypeDecoratorProperty::NODE_TYPE_FIELD_PLACEHOLDER, => $this->getStringValue(),
            NodeTypeDecoratorProperty::NODE_TYPE_FIELD_MIN_LENGTH,
            NodeTypeDecoratorProperty::NODE_TYPE_FIELD_MAX_LENGTH => $this->getIntegerValue(),
            NodeTypeDecoratorProperty::NODE_TYPE_FIELD_VISIBLE,
            NodeTypeDecoratorProperty::NODE_TYPE_FIELD_UNIVERSAL => $this->getBooleanValue(),
        };
    }

    public function getStringValue(): ?string
    {
        return null !== $this->value ? $this->value : null;
    }

    public function getBooleanValue(): ?bool
    {
        $trueValues = [
            true,
            'true',
            '1',
            1,
            'on',
            'yes',
        ];

        return null !== $this->value ? in_array($this->value, $trueValues, true) : null;
    }

    public function getIntegerValue(): ?int
    {
        return null !== $this->value ? intval($this->value) : null;
    }

    public function setValue(int|string|bool|null $value): self
    {
        $this->value = null !== $value ? (string) $value : null;

        return $this;
    }

    public function applyOn(NodeType $nodeType): void
    {
        if ($this->property->isNodeTypeProperty()) {
            switch ($this->property) {
                case NodeTypeDecoratorProperty::NODE_TYPE_COLOR:
                    $nodeType->setColor($this->getStringValue());
                    break;
                case NodeTypeDecoratorProperty::NODE_TYPE_DESCRIPTION:
                    $nodeType->setDescription($this->getStringValue());
                    break;
                case NodeTypeDecoratorProperty::NODE_TYPE_DISPLAY_NAME:
                    $nodeType->setDisplayName($this->getStringValue());
                    break;
            }
        } else {
            $nodeTypeField = $this->getNodeTypeField($nodeType);
            if (null === $nodeTypeField) {
                return;
            }
            switch ($this->property) {
                case NodeTypeDecoratorProperty::NODE_TYPE_FIELD_LABEL:
                    $nodeTypeField->setLabel($this->getStringValue());
                    break;
                case NodeTypeDecoratorProperty::NODE_TYPE_FIELD_DESCRIPTION:
                    $nodeTypeField->setDescription($this->getStringValue());
                    break;
                case NodeTypeDecoratorProperty::NODE_TYPE_FIELD_PLACEHOLDER:
                    $nodeTypeField->setPlaceholder($this->getStringValue());
                    break;
                case NodeTypeDecoratorProperty::NODE_TYPE_FIELD_MIN_LENGTH:
                    $nodeTypeField->setMinLength($this->getIntegerValue());
                    break;
                case NodeTypeDecoratorProperty::NODE_TYPE_FIELD_MAX_LENGTH:
                    $nodeTypeField->setMaxLength($this->getIntegerValue());
                    break;
                case NodeTypeDecoratorProperty::NODE_TYPE_FIELD_VISIBLE:
                    if (null !== $value = $this->getBooleanValue()) {
                        $nodeTypeField->setVisible($value);
                    }
                    break;
                case NodeTypeDecoratorProperty::NODE_TYPE_FIELD_UNIVERSAL:
                    if (null !== $value = $this->getBooleanValue()) {
                        $nodeTypeField->setUniversal($value);
                    }
                    break;
            }
        }
    }

    private function getNodeTypeField(NodeType $nodeType): ?NodeTypeField
    {
        $fieldName = explode('.', $this->path)[1] ?? '';

        return $nodeType->getFieldByName($fieldName);
    }

    public function __toString(): string
    {
        return $this->path.$this->property->value;
    }
}
