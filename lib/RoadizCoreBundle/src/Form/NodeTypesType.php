<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use RZ\Roadiz\CoreBundle\Bag\DecoratedNodeTypes;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class NodeTypesType extends AbstractType
{
    public function __construct(private readonly DecoratedNodeTypes $nodeTypesBag)
    {
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'showInvisible' => false,
            'currentType' => null,
            // Hard-code the most used node-type here
            'preferred_choices' => array_map(fn (NodeType $nodeType) => $nodeType->getName(), $this->nodeTypesBag->allHighlighted()),
        ]);
        $resolver->setAllowedTypes('showInvisible', ['boolean']);
        $resolver->setAllowedTypes('currentType', ['null', NodeType::class]);
        $resolver->setNormalizer('choices', function (Options $options, $choices) {
            $nodeTypes = $this->getNodeTypes($options);

            foreach ($nodeTypes as $nodeType) {
                if (null !== $options['currentType'] && $options['currentType']->getName() === $nodeType->getName()) {
                    continue;
                }
                $choices[$nodeType->getDisplayName()] = $nodeType->getName();
            }
            ksort($choices);

            return $choices;
        });
        $resolver->setNormalizer('group_by', fn (Options $options) => function ($choice, $key, $value) use ($options) {
            $nodeTypes = $this->getNodeTypes($options);

            foreach ($nodeTypes as $nodeType) {
                if ($value !== $nodeType->getName()) {
                    continue;
                }
                if ($nodeType->isReachable()) {
                    return 'reachable';
                }

                return 'not_reachable';
            }

            return null;
        });
    }

    /**
     * @return array<NodeType>
     */
    private function getNodeTypes(Options $options): array
    {
        if (true === $options['showInvisible']) {
            return $this->nodeTypesBag->all();
        }

        return $this->nodeTypesBag->allVisible();
    }

    #[\Override]
    public function getParent(): string
    {
        return ChoiceType::class;
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'node_types';
    }
}
