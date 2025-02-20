<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use RZ\Roadiz\CoreBundle\Form\Constraint\ValidYaml;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Yaml editor form field type.
 */
final class YamlType extends AbstractType
{
    public function getParent(): ?string
    {
        return TextareaType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'yaml';
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        $view->vars['attr']['class'] = 'yaml_textarea';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'required' => false,
            'constraints' => [
                new ValidYaml(),
            ],
        ]);
    }
}
