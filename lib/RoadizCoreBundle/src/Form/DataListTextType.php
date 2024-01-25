<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DataListTextType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setRequired('listName');
        $resolver->setAllowedTypes('listName', 'string');
        $resolver->setRequired('list');
        $resolver->setAllowedTypes('list', 'array');
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        $view->vars['listName'] = $options['listName'];
        $view->vars['list'] = $options['list'];
    }


    public function getBlockPrefix(): string
    {
        return 'data_list_text';
    }

    public function getParent(): string
    {
        return TextType::class;
    }
}
