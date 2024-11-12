<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use RZ\Roadiz\CoreBundle\Entity\RealmNode;
use RZ\Roadiz\CoreBundle\Model\RealmInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class RealmNodeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('realm', RealmChoiceType::class, [
            'label' => 'realm_node.realm',
            'help' => 'realm_node.realm.help',
            'placeholder' => 'realm_node.realm.placeholder',
            'required' => false,
        ])->add('inheritanceType', ChoiceType::class, [
            'label' => 'realm_node.inheritanceType',
            'help' => 'realm_node.inheritanceType.help',
            'required' => true,
            'choices' => [
                'realm_node.'.RealmInterface::INHERITANCE_ROOT => RealmInterface::INHERITANCE_ROOT,
                'realm_node.'.RealmInterface::INHERITANCE_AUTO => RealmInterface::INHERITANCE_AUTO,
                'realm_node.'.RealmInterface::INHERITANCE_NONE => RealmInterface::INHERITANCE_NONE,
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', RealmNode::class);
    }
}
