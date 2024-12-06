<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Form;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Form\TagsType;
use RZ\Roadiz\RozierBundle\Form\DataTransformer\NodesTagsTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class NodesTagsType extends AbstractType
{
    public function __construct(private readonly ManagerRegistry $managerRegistry)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('nodesTags', TagsType::class, [
            'by_reference' => false,
        ]);
        $builder->get('nodesTags')
            ->addViewTransformer(new NodesTagsTransformer($this->managerRegistry));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Node::class);
    }

    public function getBlockPrefix(): string
    {
        return 'node_tags';
    }
}
