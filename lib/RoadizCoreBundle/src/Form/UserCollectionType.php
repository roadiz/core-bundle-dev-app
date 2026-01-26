<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\User;
use Symfony\Bridge\Doctrine\Form\DataTransformer\CollectionToArrayTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class UserCollectionType extends AbstractType
{
    public function __construct(private readonly ManagerRegistry $managerRegistry)
    {
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new CollectionToArrayTransformer());
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'multiple' => true,
            'choice_label' => fn (?User $choice) => $choice ? $choice->getIdentifier() : '',
            'choice_value' => fn (?User $choice) => $choice ? $choice->getId() : '',
        ]);

        $resolver->setNormalizer('choices', fn () => $this->managerRegistry->getRepository(User::class)->findAll());
    }

    #[\Override]
    public function getParent(): string
    {
        return ChoiceType::class;
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'user_collection';
    }
}
