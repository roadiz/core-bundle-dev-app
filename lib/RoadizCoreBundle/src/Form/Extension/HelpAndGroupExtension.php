<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class HelpAndGroupExtension extends AbstractTypeExtension
{
    #[\Override]
    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }

    #[\Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['help'] = $options['help'] ?? '';
        $view->vars['group'] = $options['group'] ?? '';
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'help' => null,
            'group' => null,
        ]);

        $resolver->setAllowedTypes('help', ['null', 'string']);
        $resolver->setAllowedTypes('group', ['null', 'string']);
    }
}
