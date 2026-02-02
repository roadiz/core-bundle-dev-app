<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Form;

use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Form\NodesType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserSecurityType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('enabled', CheckboxType::class, [
            'label' => 'user.enabled',
            'required' => false,
        ])
            ->add('locked', CheckboxType::class, [
                'label' => 'user.locked',
                'required' => false,
            ])
            ->add('expiresAt', DateTimeType::class, [
                'label' => 'user.expiresAt',
                'required' => false,
                'html5' => true,
            ])
            ->add('credentialsExpiresAt', DateTimeType::class, [
                'label' => 'user.credentialsExpiresAt',
                'required' => false,
                'html5' => true,
            ]);

        if (true === $options['canChroot']) {
            $builder->add('chroot', NodesType::class, [
                'label' => 'chroot',
                'asMultiple' => false,
                'required' => false,
            ]);
        }
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'user_security';
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => false,
            'data_class' => User::class,
            'canChroot' => false,
            'attr' => [
                'class' => 'rz-form user-form',
            ],
        ]);

        $resolver->setAllowedTypes('canChroot', ['bool']);
    }
}
