<?php

declare(strict_types=1);

namespace Themes\Rozier\Forms;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Form\TagsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Themes\Rozier\Forms\DataTransformer\TagTransformer;

/**
 * @package Themes\Rozier\Forms
 * @deprecated
 */
class NodeTagsType extends AbstractType
{
    protected ManagerRegistry $managerRegistry;

    /**
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * {@inheritdoc}
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('tags', TagsType::class, [
            'by_reference' => false
        ]);
        $builder->get('tags')
            ->addModelTransformer(new TagTransformer($this->managerRegistry->getManager()));
    }

    /**
     * {@inheritdoc}
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Node::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'node_tags';
    }
}
