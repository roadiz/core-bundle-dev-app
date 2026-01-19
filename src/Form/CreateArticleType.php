<?php

declare(strict_types=1);

namespace App\Form;

use App\Model\CreateArticleInput;
use RZ\Roadiz\CoreBundle\Form\DataTransformer\TagArrayTransformer;
use RZ\Roadiz\CoreBundle\Form\TagsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

final class CreateArticleType extends AbstractType
{
    public function __construct(
        private readonly TagArrayTransformer $tagArrayTransformer,
    ) {
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'title',
                'constraints' => [
                    new NotNull(),
                    new NotBlank(),
                ],
            ])
            ->add('tags', TagsType::class, [
                'label' => 'tags',
                'required' => false,
            ])
        ;

        $builder->get('tags')->addModelTransformer($this->tagArrayTransformer);
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', CreateArticleInput::class);
    }
}
