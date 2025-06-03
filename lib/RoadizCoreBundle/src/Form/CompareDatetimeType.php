<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CompareDatetimeType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('compareOp', ChoiceType::class, [
            'label' => false,
            'choices' => [
                '<' => '<',
                '>' => '>',
                '<=' => '<=',
                '>=' => '>=',
                '=' => '=',
            ],
        ])
        ->add('compareDatetime', DateTimeType::class, [
            'label' => false,
            'required' => false,
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
    }

    #[\Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        $view->vars['attr']['class'] = 'rz-compare-datetype';
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'inherit_data' => false,
            'required' => false,
        ]);
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'comparedatetime';
    }
}
