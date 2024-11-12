<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Realm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class RealmChoiceType extends AbstractType
{
    public function __construct(private readonly ManagerRegistry $managerRegistry)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'multiple' => false,
            'choice_label' => function (?Realm $choice) {
                return $choice ? $choice->getName() : '';
            },
            'choice_value' => function (?Realm $choice) {
                return $choice ? $choice->getId() : '';
            },
        ]);

        $resolver->setNormalizer('choices', function () {
            return $this->managerRegistry->getRepository(Realm::class)->findAll();
        });
    }

    public function getParent(): ?string
    {
        return ChoiceType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'realms';
    }
}
