<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use RZ\Roadiz\CoreBundle\Entity\Realm;
use RZ\Roadiz\CoreBundle\Model\RealmInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class RealmType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', TextType::class, [
            'label' => 'name',
            'empty_data' => '',
            'by_reference' => true,
            'required' => true,
        ])->add('type', ChoiceType::class, [
            'label' => 'realm.type',
            'help' => 'realm.type.help',
            'required' => true,
            'choices' => [
                'realm.'.RealmInterface::TYPE_PLAIN_PASSWORD => RealmInterface::TYPE_PLAIN_PASSWORD,
                'realm.'.RealmInterface::TYPE_ROLE => RealmInterface::TYPE_ROLE,
                'realm.'.RealmInterface::TYPE_USER => RealmInterface::TYPE_USER,
            ],
        ])->add('behaviour', ChoiceType::class, [
            'label' => 'realm.behaviour',
            'help' => 'realm.behaviour.help',
            'required' => true,
            'choices' => [
                'realm.behaviour_'.RealmInterface::BEHAVIOUR_NONE => RealmInterface::BEHAVIOUR_NONE,
                'realm.behaviour_'.RealmInterface::BEHAVIOUR_DENY => RealmInterface::BEHAVIOUR_DENY,
                'realm.behaviour_'.RealmInterface::BEHAVIOUR_HIDE_BLOCKS => RealmInterface::BEHAVIOUR_HIDE_BLOCKS,
            ],
        ])->add('plainPassword', TextType::class, [
            'label' => 'realm.plainPassword',
            'help' => 'realm.plainPassword.help',
            'empty_data' => null,
            'required' => false,
        ])->add('serializationGroup', TextType::class, [
            'label' => 'realm.serializationGroup',
            'help' => 'realm.serializationGroup.help',
            'empty_data' => null,
            'by_reference' => true,
            'required' => false,
        ])->add('role', RolesType::class, [
            'label' => 'realm.role',
            'help' => 'realm.role.help',
            'multiple' => false,
            'required' => false,
            'placeholder' => 'realm.role.placeholder',
        ])->add('users', UserCollectionType::class, [
            'label' => 'realm.users',
            'help' => 'realm.users.help',
            'required' => false,
        ]);
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Realm::class);
        $resolver->setDefault('constraints', [
            new UniqueEntity(['name']),
            new UniqueEntity(['serializationGroup']),
        ]);
    }
}
