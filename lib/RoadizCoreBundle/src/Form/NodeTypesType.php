<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Node types selector form field type.
 */
class NodeTypesType extends AbstractType
{
    public function __construct(private readonly NodeTypes $nodeTypesBag)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'showInvisible' => false,
        ]);
        $resolver->setAllowedTypes('showInvisible', ['boolean']);
        $resolver->setNormalizer('choices', function (Options $options, $choices) {
            if (true === $options['showInvisible']) {
                $nodeTypes = $this->nodeTypesBag->all();
            } else {
                $nodeTypes = $this->nodeTypesBag->allVisible();
            }

            foreach ($nodeTypes as $nodeType) {
                $choices[$nodeType->getDisplayName()] = $nodeType->getName();
            }
            ksort($choices);

            return $choices;
        });
    }

    public function getParent(): ?string
    {
        return ChoiceType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'node_types';
    }
}
