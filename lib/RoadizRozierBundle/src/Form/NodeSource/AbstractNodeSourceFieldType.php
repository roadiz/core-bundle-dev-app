<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Form\NodeSource;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractNodeSourceFieldType extends AbstractType
{
    public function __construct(protected readonly ManagerRegistry $managerRegistry)
    {
    }

    /**
     * Pass nodeSource to form twig template.
     */
    #[\Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        $view->vars['nodeSource'] = $options['nodeSource'];
        $view->vars['nodeTypeField'] = $options['nodeTypeField'];
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setRequired([
            'nodeSource',
            'nodeTypeField',
        ]);

        $resolver->setAllowedTypes('nodeSource', [NodesSources::class]);
        $resolver->setAllowedTypes('nodeTypeField', [NodeTypeField::class]);
    }

    #[\Override]
    public function getParent(): ?string
    {
        return HiddenType::class;
    }
}
