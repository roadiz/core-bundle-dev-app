<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use RZ\Roadiz\CoreBundle\Form\DataTransformer\ExplorerProviderItemTransformer;
use RZ\Roadiz\CoreBundle\Explorer\ExplorerProviderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @package RZ\Roadiz\CoreBundle\Form
 */
class ExplorerProviderItemType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new ExplorerProviderItemTransformer(
            $options['explorerProvider'],
            $options['asMultiple'],
            $options['useCollection'],
        ));
    }

    /**
     * Pass data to form twig template.
     *
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        if ($options['max_length'] > 0) {
            $view->vars['attr']['data-max-length'] = $options['max_length'];
        }
        if ($options['min_length'] > 0) {
            $view->vars['attr']['data-min-length'] = $options['min_length'];
        }
        if ($options['asMultiple'] === false) {
            $view->vars['attr']['data-max-length'] = 1;
        }

        $view->vars['provider_class'] = get_class($options['explorerProvider']);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'explorer_provider';
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setRequired('explorerProvider');

        $resolver->setDefault('max_length', 0);
        $resolver->setDefault('min_length', 0);
        $resolver->setDefault('multiple', true);
        $resolver->setDefault('asMultiple', true);
        $resolver->setDefault('useCollection', false);

        $resolver->setAllowedTypes('explorerProvider', [ExplorerProviderInterface::class]);
        $resolver->setAllowedTypes('max_length', ['int']);
        $resolver->setAllowedTypes('min_length', ['int']);
        $resolver->setAllowedTypes('asMultiple', ['bool']);
        $resolver->setAllowedTypes('useCollection', ['bool']);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): ?string
    {
        return HiddenType::class;
    }
}
