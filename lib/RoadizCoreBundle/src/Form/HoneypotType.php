<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class HoneypotType extends AbstractType
{
    public function getParent(): ?string
    {
        return TextType::class;
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        $view->vars['attr'] = [
            'autocomplete' => 'nope',
            'tabindex' => -1,
            'style' => 'position: fixed; left: -100vw; top: -100vh;',
        ];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => false,
            'required' => false,
            'mapped' => false,
            'data' => '',
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();
            if (!$data) {
                return;
            }
            $form->getParent()->addError(new FormError('form_has_errors.check_you_fields'));
        });
    }
}
