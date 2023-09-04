<?php

declare(strict_types=1);

namespace Themes\Rozier\Forms;

use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Form\NodesType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserSecurityType extends AbstractType
{
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
                'years' => range(date('Y'), ((int) date('Y')) + 2),
                'date_widget' => 'single_text',
                'date_format' => 'yyyy-MM-dd',
                'attr' => [
                    'class' => 'rz-datetime-field',
                ],
                'placeholder' => [
                    'hour' => 'hour',
                    'minute' => 'minute',
                ],
            ])
            ->add('credentialsExpiresAt', DateTimeType::class, [
                'label' => 'user.credentialsExpiresAt',
                'required' => false,
                'years' => range(date('Y'), ((int) date('Y')) + 2),
                'date_widget' => 'single_text',
                'date_format' => 'yyyy-MM-dd',
                'attr' => [
                    'class' => 'rz-datetime-field',
                ],
                'placeholder' => [
                    'hour' => 'hour',
                    'minute' => 'minute',
                ],
            ]);

        if ($options['canChroot'] === true) {
            $builder->add('chroot', NodesType::class, [
                'label' => 'chroot',
                'required' => false,
            ]);
            $builder->get('chroot')->addModelTransformer(new CallbackTransformer(
                function (mixed $mixedEntities) {
                    if ($mixedEntities instanceof Node) {
                        return [$mixedEntities];
                    }
                    return [];
                },
                function (mixed $mixedIds) {
                    if (\is_array($mixedIds) && count($mixedIds) === 1) {
                        return $mixedIds[0];
                    }
                    return null;
                }
            ));
        }
    }

    public function getBlockPrefix(): string
    {
        return 'user_security';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => false,
            'data_class' => User::class,
            'canChroot' => false,
            'attr' => [
                'class' => 'uk-form user-form',
            ],
        ]);

        $resolver->setAllowedTypes('canChroot', ['bool']);
    }
}
