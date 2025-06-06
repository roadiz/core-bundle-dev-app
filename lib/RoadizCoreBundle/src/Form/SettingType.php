<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use RZ\Roadiz\CoreBundle\Entity\Setting;
use RZ\Roadiz\CoreBundle\Enum\FieldType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Type;

final class SettingType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (false === $options['shortEdit']) {
            $builder
                ->add('name', TextType::class, [
                    'empty_data' => '',
                    'label' => 'name',
                ])
                ->add('description', MarkdownType::class, [
                    'label' => 'description',
                    'required' => false,
                ])
                ->add('visible', CheckboxType::class, [
                    'label' => 'visible',
                    'required' => false,
                ])
                ->add('type', EnumType::class, [
                    'label' => 'type',
                    'required' => true,
                    'class' => FieldType::class,
                    'choice_label' => fn (FieldType $fieldType) => $fieldType->toHuman(),
                    'choice_filter' => fn (FieldType $fieldType) => \in_array($fieldType, Setting::$availableTypes),
                ])
                ->add('settingGroup', SettingGroupType::class, [
                    'label' => 'setting.group',
                    'required' => false,
                ])
                ->add('defaultValues', TextType::class, [
                    'label' => 'defaultValues',
                    'attr' => [
                        'placeholder' => 'enter_values_comma_separated',
                    ],
                    'required' => false,
                ])
            ;
        }

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            /** @var Setting|null $setting */
            $setting = $event->getData();
            $form = $event->getForm();

            if ($setting instanceof Setting) {
                if ($setting->isDocuments()) {
                    $form->add(
                        'value',
                        SettingDocumentType::class,
                        [
                            'label' => (!$options['shortEdit']) ? 'value' : false,
                            'required' => false,
                        ]
                    );
                } else {
                    $form->add(
                        'value',
                        $setting->getType()->toFormType(),
                        $this->getFormOptionsForSetting($setting, $options['shortEdit'])
                    );
                }
            } else {
                $form->add('value', TextType::class, [
                    'label' => 'value',
                    'required' => false,
                ]);
            }
        });
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Setting::class);
        $resolver->setDefault('shortEdit', false);
        $resolver->setAllowedTypes('shortEdit', ['boolean']);
    }

    protected function getFormOptionsForSetting(Setting $setting, bool $shortEdit = false): array
    {
        $label = (!$shortEdit) ? 'value' : false;

        switch ($setting->getType()) {
            case FieldType::ENUM_T:
            case FieldType::MULTIPLE_T:
                $values = explode(',', $setting->getDefaultValues() ?? '');
                $values = array_map(fn ($item) => trim((string) $item), $values);

                return [
                    'label' => $label,
                    'placeholder' => 'choose.value',
                    'required' => false,
                    'choices' => array_combine($values, $values),
                    'multiple' => $setting->isMultiple(),
                ];
            case FieldType::EMAIL_T:
                return [
                    'label' => $label,
                    'required' => false,
                    'constraints' => [
                        new Email(),
                    ],
                ];
            case FieldType::DATETIME_T:
                return [
                    'placeholder' => [
                        'hour' => 'hour',
                        'minute' => 'minute',
                    ],
                    'date_widget' => 'single_text',
                    'date_format' => 'yyyy-MM-dd',
                    'attr' => [
                        'class' => 'rz-datetime-field',
                    ],
                    'label' => $label,
                    'years' => range((int) date('Y') - 10, (int) date('Y') + 10),
                    'required' => false,
                ];
            case FieldType::INTEGER_T:
                return [
                    'label' => $label,
                    'required' => false,
                    'constraints' => [
                        new Type('integer'),
                    ],
                ];
            case FieldType::DECIMAL_T:
                return [
                    'label' => $label,
                    'required' => false,
                    'constraints' => [
                        new Type('double'),
                    ],
                ];
            default:
                return [
                    'label' => $label,
                    'required' => false,
                ];
        }
    }
}
