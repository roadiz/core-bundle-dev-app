<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Form;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\RozierBundle\TranslateAssistant\NullTranslateAssistant;
use RZ\Roadiz\RozierBundle\TranslateAssistant\TranslateAssistantInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TranslateNodeType extends AbstractType
{
    public function __construct(
        protected ManagerRegistry $managerRegistry,
        protected TranslateAssistantInterface $translateAssistant,
    ) {
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Subtree-wide: a root that is already translated must still offer the language when
        // some of its descendants are missing it, otherwise a half-done run cannot be resumed.
        $translations = $this->managerRegistry
            ->getRepository(Translation::class)
            ->findIncompleteTranslationsForNodes($options['subtreeNodeIds']);
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

        if (!$this->translateAssistant instanceof NullTranslateAssistant) {
            $builder
                ->add('use_translate_assistant', CheckboxType::class, [
                    'label' => 'use_translate_assistant',
                    'help' => 'use_translate_assistant.help',
                    'required' => false,
                ])
                ->add('dry_run', CheckboxType::class, [
                    'label' => 'translate_assistant.dry_run',
                    'help' => 'translate_assistant.dry_run.help',
                    'required' => false,
                ]);
        }
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
            'subtreeNodeIds',
        ]);
        $resolver->setAllowedTypes('node', Node::class);
        $resolver->setAllowedTypes('subtreeNodeIds', 'int[]');
    }
}
