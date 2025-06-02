<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\SettingGroup;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class SettingGroupType extends AbstractType
{
    public function __construct(private readonly ManagerRegistry $managerRegistry)
    {
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new CallbackTransformer(
            function (?SettingGroup $settingGroup = null) {
                if (null !== $settingGroup) {
                    // transform the array to a string
                    return $settingGroup->getId();
                }

                return null;
            },
            function ($id) {
                if (null !== $id) {
                    $manager = $this->managerRegistry->getManagerForClass(SettingGroup::class);

                    return $manager->find(SettingGroup::class, $id);
                }

                return null;
            }
        ));
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => [],
            'placeholder' => '---------',
        ]);

        /*
         * Use normalizer to populate choices from ChoiceType
         */
        $resolver->setNormalizer('choices', function (Options $options, $choices) {
            $groups = $this->managerRegistry->getRepository(SettingGroup::class)->findAll();
            /** @var SettingGroup $group */
            foreach ($groups as $group) {
                $choices[$group->getName()] = $group->getId();
            }

            return $choices;
        });
    }

    #[\Override]
    public function getParent(): ?string
    {
        return ChoiceType::class;
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'setting_groups';
    }
}
