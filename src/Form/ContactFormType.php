<?php

declare(strict_types=1);

namespace App\Form;

use RZ\Roadiz\CoreBundle\Form\MarkdownType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotNull;

final class ContactFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('last_name', TextType::class, [
                'label' => 'contact_form.last_name',
                'required' => true,
                'constraints' => [
                    new NotNull(),
                ],
            ])
            ->add('first_name', TextType::class, [
                'label' => 'contact_form.first_name',
                'required' => true,
                'constraints' => [
                    new NotNull(),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'contact_form.email',
                'required' => true,
                'constraints' => [
                    new NotNull(),
                    new Email(),
                ],
            ])
            ->add('message', MarkdownType::class, [
                'label' => 'contact_form.message',
                'required' => true,
                'constraints' => [
                    new NotNull(),
                ],
            ])
            ->add('file', FileType::class, [
                'label' => 'contact_form.file',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('label', false);
        $resolver->setDefault('required', false);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
