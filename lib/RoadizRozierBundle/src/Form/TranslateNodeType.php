<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Form;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TranslateNodeType extends AbstractType
{
    public function __construct(protected ManagerRegistry $managerRegistry)
    {
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $translations = $this->managerRegistry
            ->getRepository(Translation::class)
            ->findUnavailableTranslationsForNode($options['node']);
        $availableTranslations = $this->managerRegistry
            ->getRepository(Translation::class)
            ->findAvailableTranslationsForNode($options['node']);

        $builder
            ->add('sourceTranslation', ChoiceType::class, [
                'label' => 'source_translation',
                'help' => 'source_translation.help',
                'choices' => $availableTranslations,
                'required' => true,
                'multiple' => false,
                'choice_value' => 'id',
                'choice_label' => 'name',
            ])
            ->add('translation', ChoiceType::class, [
                'label' => 'destination_translation',
                'choices' => $translations,
                'required' => true,
                'multiple' => false,
                'choice_value' => 'id',
                'choice_label' => 'name',
            ])
            ->add('translate_offspring', CheckboxType::class, [
                'label' => 'translate_offspring',
                'help' => 'translate_offspring.help',
                'required' => false,
            ]);
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'translate_node';
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => false,
            'attr' => [
                'class' => 'rz-form node-translation-form',
            ],
        ]);

        $resolver->setRequired([
            'node',
        ]);
        $resolver->setAllowedTypes('node', Node::class);
    }
}
