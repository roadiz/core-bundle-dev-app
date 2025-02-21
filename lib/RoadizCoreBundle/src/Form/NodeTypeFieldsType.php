<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use RZ\Roadiz\CoreBundle\Bag\DecoratedNodeTypes;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NodeTypeFieldsType extends AbstractType
{
    public function __construct(private readonly DecoratedNodeTypes $nodeTypesBag)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'nodeType' => null,
            'choice_label' => fn (NodeTypeField $field) => $field->getLabel(),
            'choice_value' => fn (?NodeTypeField $field) => $field ? $field->getName() : '',
            'group_by' => fn (NodeTypeField $field) => $field->getNodeType()->getName(),
        ]);
        $resolver->setAllowedTypes('nodeType', [NodeType::class, 'null']);
        $resolver->setNormalizer('choices', function (Options $options) {
            if (null !== $options['nodeType']) {
                return $options['nodeType']->getFields();
            } else {
                $nodeTypeFields = [];
                foreach ($this->nodeTypesBag->all() as $nodeType) {
                    $nodeTypeFields = [
                        ...$nodeTypeFields,
                        ...$nodeType->getFields()->toArray(),
                    ];
                }

                return $nodeTypeFields;
            }
        });
    }

    public function getParent(): ?string
    {
        return ChoiceType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'node_type_fields';
    }
}
