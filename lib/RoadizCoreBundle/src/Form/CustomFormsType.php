<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use RZ\Roadiz\CoreBundle\Captcha\CaptchaServiceInterface;
use RZ\Roadiz\CoreBundle\Entity\CustomForm;
use RZ\Roadiz\CoreBundle\Entity\CustomFormField;
use RZ\Roadiz\CoreBundle\Enum\FieldType;
use RZ\Roadiz\Utils\StringHandler;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

final class CustomFormsType extends AbstractType
{
    public function __construct(
        private readonly CaptchaServiceInterface $captchaService,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $fieldsArray = $this->getFieldsByGroups($options);

        /** @var CustomFormField|array $field */
        foreach ($fieldsArray as $group => $field) {
            if ($field instanceof CustomFormField) {
                $this->addSingleField($builder, $field, $options);
            } elseif (is_array($field)) {
                $groupCanonical = StringHandler::slugify($group);
                $subBuilder = $builder->create($groupCanonical, FormType::class, [
                    'label' => $group,
                    'inherit_data' => true,
                    'attr' => [
                        'data-group-wrapper' => $groupCanonical,
                    ],
                ]);
                /** @var CustomFormField $subfield */
                foreach ($field as $subfield) {
                    $this->addSingleField($subBuilder, $subfield, $options);
                }
                $builder->add($subBuilder);
            }
        }

        if ($this->captchaService->isEnabled()) {
            $builder->add($this->captchaService->getFieldName(), CaptchaType::class);
        }
    }

    protected function getFieldsByGroups(array $options): array
    {
        $fieldsArray = [];
        $fields = $options['customForm']->getFields();

        /** @var CustomFormField $field */
        foreach ($fields as $field) {
            $groupName = $field->getGroupName();
            if (\is_string($groupName) && '' !== $groupName) {
                if (!isset($fieldsArray[$groupName]) || !\is_array($fieldsArray[$groupName])) {
                    $fieldsArray[$groupName] = [];
                }
                $fieldsArray[$groupName][] = $field;
            } else {
                $fieldsArray[] = $field;
            }
        }

        return $fieldsArray;
    }

    /**
     * @return $this
     */
    protected function addSingleField(FormBuilderInterface $builder, CustomFormField $field, array $formOptions): self
    {
        $builder->add(
            $field->getName(),
            $this->getTypeForField($field),
            $this->getOptionsForField($field, $formOptions)
        );

        return $this;
    }

    /**
     * @return class-string<AbstractType>
     */
    protected function getTypeForField(CustomFormField $field): string
    {
        return $field->getType()->toFormType();
    }

    /**
     * @return array<string, mixed>
     */
    protected function getOptionsForField(CustomFormField $field, array $formOptions): array
    {
        $option = [
            'label' => $field->getLabel(),
            'help' => $field->getDescription(),
            'attr' => [
                'data-group' => $field->getGroupName(),
            ],
        ];

        if (!empty($field->getPlaceholder())) {
            $option['attr']['placeholder'] = $field->getPlaceholder();
        }

        if (null !== $field->getAutocomplete()) {
            $option['attr']['autocomplete'] = $field->getAutocomplete();
        }

        if ($field->isRequired()) {
            $option['required'] = true;
            $option['constraints'] = [
                new NotBlank([
                    'message' => 'you.need.to.fill.this.required.field',
                ]),
            ];
        } else {
            $option['required'] = false;
        }

        switch ($field->getType()) {
            case FieldType::DATETIME_T:
                $option['widget'] = 'single_text';
                $option['format'] = DateTimeType::HTML5_FORMAT;
                break;
            case FieldType::DATE_T:
                $option['widget'] = 'single_text';
                $option['format'] = DateType::HTML5_FORMAT;
                break;
            case FieldType::ENUM_T:
                if (!empty($field->getPlaceholder())) {
                    $option['placeholder'] = $field->getPlaceholder();
                }
                $option['choices'] = $this->getChoices($field);
                $option['expanded'] = $field->isExpanded();

                if ($formOptions['forceExpanded']) {
                    $option['expanded'] = true;
                }
                if (false === $field->isRequired()) {
                    $option['placeholder'] = 'none';
                }
                break;
            case FieldType::MULTIPLE_T:
                if (!empty($field->getPlaceholder())) {
                    $option['placeholder'] = $field->getPlaceholder();
                }
                $option['choices'] = $this->getChoices($field);
                $option['multiple'] = true;
                $option['expanded'] = $field->isExpanded();

                if ($formOptions['forceExpanded']) {
                    $option['expanded'] = true;
                }
                if (false === $field->isRequired()) {
                    $option['placeholder'] = 'none';
                }
                break;
            case FieldType::DOCUMENTS_T:
                $option['multiple'] = true;
                $option['mapped'] = false;
                $mimeTypes = [
                    'application/pdf',
                    'application/x-pdf',
                    'image/avif',
                    'image/heif',
                    'image/heic',
                    'image/webp',
                    'image/jpeg',
                    'image/png',
                    'image/gif',
                ];
                if (!empty($field->getDefaultValues())) {
                    $mimeTypes = $field->getDefaultValuesAsArray();
                }
                $option['constraints'][] = new All([
                    'constraints' => [
                        new File([
                            'maxSize' => $formOptions['fileUploadMaxSize'],
                            'mimeTypes' => $mimeTypes,
                        ]),
                    ],
                ]);
                break;
            case FieldType::COUNTRY_T:
                $option['expanded'] = $field->isExpanded();
                if (!empty($field->getPlaceholder())) {
                    $option['placeholder'] = $field->getPlaceholder();
                }
                if (!empty($field->getDefaultValues())) {
                    $countries = $field->getDefaultValuesAsArray();
                    $option['preferred_choices'] = $countries;
                }
                break;
            case FieldType::EMAIL_T:
                if (!isset($option['constraints'])) {
                    $option['constraints'] = [];
                }
                $option['constraints'][] = new Email();
                break;
            default:
                break;
        }

        return $option;
    }

    protected function getChoices(CustomFormField $field): array
    {
        $choices = $field->getDefaultValuesAsArray();

        return array_combine(array_values($choices), array_values($choices));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'forceExpanded' => false,
            'csrf_protection' => false,
            // You may reduce this value when you have multiple files upload fields
            // to avoid hitting email server upload limit.
            'fileUploadMaxSize' => '10m',
        ]);

        $resolver->setRequired('customForm');
        $resolver->setAllowedTypes('customForm', [CustomForm::class]);
        $resolver->setAllowedTypes('forceExpanded', ['boolean']);
        $resolver->setAllowedTypes('fileUploadMaxSize', ['string']);
    }

    public function getBlockPrefix(): string
    {
        return 'custom_form_public';
    }
}
