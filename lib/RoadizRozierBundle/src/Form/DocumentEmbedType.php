<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DocumentEmbedType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
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
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('required', true);
        $resolver->setRequired('document_platforms');
        $resolver->setAllowedTypes('document_platforms', ['array']);
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'document_embed';
    }
}
