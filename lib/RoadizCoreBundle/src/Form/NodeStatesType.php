<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use RZ\Roadiz\CoreBundle\Entity\Node;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Node state selector form field type.
 */
class NodeStatesType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $choices = [];
        $choices[Node::getStatusLabel(Node::DRAFT)] = Node::DRAFT;
        $choices[Node::getStatusLabel(Node::PENDING)] = Node::PENDING;
        $choices[Node::getStatusLabel(Node::PUBLISHED)] = Node::PUBLISHED;
        $choices[Node::getStatusLabel(Node::ARCHIVED)] = Node::ARCHIVED;
        $choices[Node::getStatusLabel(Node::DELETED)] = Node::DELETED;

        $resolver->setDefaults([
            'choices' => $choices,
            'placeholder' => 'ignore',
        ]);
    }

    public function getParent(): ?string
    {
        return ChoiceType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'node_statuses';
    }
}
