<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Form;

use RZ\Roadiz\CoreBundle\Entity\CustomForm;
use RZ\Roadiz\CoreBundle\Form\MarkdownType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class CustomFormType extends AbstractType
{
    public function __construct(
        private readonly Security $security,
    ) {
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('displayName', TextType::class, [
            'label' => 'customForm.displayName',
            'empty_data' => '',
        ])
            ->add('description', MarkdownType::class, [
                'label' => 'description',
                'required' => false,
            ])
            ->add('email', TextType::class, [
                'label' => 'email',
                'help' => 'customForm.email.help',
                'required' => false,
                'constraints' => [
                    new Callback(function ($value, ExecutionContextInterface $context) {
                        $emails = array_filter(
                            array_map('trim', explode(',', $value ?? ''))
                        );
                        foreach ($emails as $email) {
                            if (false === filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                $context->buildViolation('{{ value }} is not a valid email address.')
                                    ->setParameter('{{ value }}', $email)
                                    ->setCode(Email::INVALID_FORMAT_ERROR)
                                    ->addViolation();
                            }
                        }
                    }),
                ],
            ])
        ;
        if ($this->security->isGranted('ROLE_ACCESS_CUSTOMFORMS_RETENTION')) {
            $builder->add('retentionTime', ChoiceType::class, [
                'label' => 'customForm.retentionTime',
                'help' => 'customForm.retentionTime.help',
                'required' => false,
                'placeholder' => 'customForm.retentionTime.always',
                'choices' => [
                    'customForm.retentionTime.one_week' => 'P7D',
                    'customForm.retentionTime.two_weeks' => 'P14D',
                    'customForm.retentionTime.one_month' => 'P1M',
                    'customForm.retentionTime.three_months' => 'P3M',
                    'customForm.retentionTime.six_months' => 'P6M',
                    'customForm.retentionTime.one_year' => 'P1Y',
                    'customForm.retentionTime.two_years' => 'P2Y',
                ],
            ]);
        }
        $builder->add('open', CheckboxType::class, [
            'label' => 'customForm.open',
            'required' => false,
        ])
            ->add('closeDate', DateTimeType::class, [
                'label' => 'customForm.closeDate',
                'required' => false,
                'date_widget' => 'single_text',
                'date_format' => 'yyyy-MM-dd',
                'attr' => [
                    'class' => 'rz-datetime-field',
                ],
                'placeholder' => [
                    'hour' => 'hour',
                    'minute' => 'minute',
                ],
            ])
            ->add('color', ColorType::class, [
                'label' => 'customForm.color',
                'required' => false,
                'html5' => true,
            ]);
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'customform';
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => false,
            'name' => '',
            'data_class' => CustomForm::class,
            'attr' => [
                'class' => 'uk-form custom-form-form',
            ],
        ]);
        $resolver->setAllowedTypes('name', 'string');
    }
}
