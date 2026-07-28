<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class DocumentEmbedType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (isset($options['document_platforms'])) {
            $services = [];
            foreach (array_keys($options['document_platforms']) as $value) {
                $value = (string) $value;
                $services[ucwords($value)] = $value;
            }

            $builder
                ->add('embedId', TextType::class, [
                    'label' => 'document.embedId',
                    'required' => true,
                ])
                ->add('embedPlatform', ChoiceType::class, [
                    'label' => 'document.platform',
                    'required' => true,
                    'choices' => $services,
                    'placeholder' => 'document.no_embed_platform',
                ])
            ;
            if (false === $options['required']) {
                $builder->get('embedId')->setRequired(false);
                $builder->get('embedPlatform')->setRequired(false);
            }
        } else {
            $builder
                ->add('embedUrl', TextType::class, [
                    'label' => 'document.embedUrl',
                    'help' => 'document.embedUrl.help',
                    'required' => true,
                    'constraints' => [
                        new NotNull(),
                        new NotBlank(),
                    ],
                ])
            ;
            $builder->get('embedUrl')->setRequired(false);
        }
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'required' => true,
            'document_platforms' => null,
        ]);
        $resolver->setAllowedTypes('document_platforms', ['array', 'null']);
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'document_embed';
    }
}
