<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use RZ\Roadiz\CoreBundle\Form\Constraint\Recaptcha;
use RZ\Roadiz\Core\AbstractEntities\AbstractField;
use RZ\Roadiz\CoreBundle\Entity\CustomForm;
use RZ\Roadiz\CoreBundle\Entity\CustomFormField;
use RZ\Roadiz\Utils\StringHandler;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @package RZ\Roadiz\CoreBundle\Form
 */
class CustomFormsType extends AbstractType
{
    protected ?string $recaptchaPrivateKey;
    protected ?string $recaptchaPublicKey;

    public function __construct(?string $recaptchaPrivateKey, ?string $recaptchaPublicKey)
    {
        $this->recaptchaPrivateKey = $recaptchaPrivateKey;
        $this->recaptchaPublicKey = $recaptchaPublicKey;
    }

    /**
     * @param  FormBuilderInterface $builder
     * @param  array $options
     */
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
                    ]
                ]);
                /** @var CustomFormField $subfield */
                foreach ($field as $subfield) {
                    $this->addSingleField($subBuilder, $subfield, $options);
                }
                $builder->add($subBuilder);
            }
        }

        /*
         * Add Google Recaptcha if setting optional options.
         */
        if (
            !empty($this->recaptchaPublicKey) &&
            !empty($this->recaptchaPrivateKey)
        ) {
            $builder->add($options['recaptcha_name'], RecaptchaType::class, [
                'label' => false,
                'configs' => [
                    'publicKey' => $this->recaptchaPublicKey,
                ],
                'constraints' => [
                    new Recaptcha([
                        'privateKey' => $this->recaptchaPrivateKey,
                        'fieldName' => $options['recaptcha_name']
                    ]),
                ],
            ]);
        }
    }

    /**
     * @param array $options
     * @return array
     */
    protected function getFieldsByGroups(array $options): array
    {
        $fieldsArray = [];
        $fields = $options['customForm']->getFields();

        /** @var CustomFormField $field */
        foreach ($fields as $field) {
            if ($field->getGroupName() != '') {
                if (!isset($fieldsArray[$field->getGroupName()])) {
                    $fieldsArray[$field->getGroupName()] = [];
                }
                $fieldsArray[$field->getGroupName()][] = $field;
            } else {
                $fieldsArray[] = $field;
            }
        }

        return $fieldsArray;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param CustomFormField $field
     * @param array $formOptions
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
     * @param CustomFormField $field
     * @return class-string<AbstractType>
     */
    protected function getTypeForField(CustomFormField $field): string
    {
        switch ($field->getType()) {
            case AbstractField::ENUM_T:
            case AbstractField::MULTIPLE_T:
            case AbstractField::RADIO_GROUP_T:
            case AbstractField::CHECK_GROUP_T:
                return ChoiceType::class;
            case AbstractField::DOCUMENTS_T:
                return FileType::class;
            case AbstractField::MARKDOWN_T:
                return MarkdownType::class;
            case AbstractField::COLOUR_T:
                return ColorType::class;
            case AbstractField::DATETIME_T:
                return DateTimeType::class;
            case AbstractField::DATE_T:
                return DateType::class;
            case AbstractField::RICHTEXT_T:
            case AbstractField::TEXT_T:
                return TextareaType::class;
            case AbstractField::BOOLEAN_T:
                return CheckboxType::class;
            case AbstractField::INTEGER_T:
                return IntegerType::class;
            case AbstractField::DECIMAL_T:
                return NumberType::class;
            case AbstractField::EMAIL_T:
                return EmailType::class;
            case AbstractField::COUNTRY_T:
                return CountryType::class;
            default:
                return TextType::class;
        }
    }

    /**
     * @param CustomFormField $field
     * @param array $formOptions
     * @return array<string, mixed>
     */
    protected function getOptionsForField(CustomFormField $field, array $formOptions): array
    {
        $option = [
            "label" => $field->getLabel(),
            'help' => $field->getDescription(),
            'attr' => [
                'data-group' => $field->getGroupName(),
            ],
        ];

        if ($field->getPlaceholder() !== '') {
            $option['attr']['placeholder'] = $field->getPlaceholder();
        }

        if ($field->isRequired()) {
            $option['required'] = true;
            $option['constraints'] = [
                new NotBlank([
                    'message' => 'you.need.to.fill.this.required.field'
                ])
            ];
        } else {
            $option['required'] = false;
        }

        switch ($field->getType()) {
            case AbstractField::DATETIME_T:
                $option["widget"] = 'single_text';
                $option["format"] = DateTimeType::HTML5_FORMAT;
                break;
            case AbstractField::DATE_T:
                $option["widget"] = 'single_text';
                $option["format"] = DateType::HTML5_FORMAT;
                break;
            case AbstractField::ENUM_T:
                if ($field->getPlaceholder() !== '') {
                    $option['placeholder'] = $field->getPlaceholder();
                }
                $option["choices"] = $this->getChoices($field);
                $option["expanded"] = $field->isExpanded();

                if ($formOptions['forceExpanded']) {
                    $option["expanded"] = true;
                }
                if ($field->isRequired() === false) {
                    $option['placeholder'] = 'none';
                }
                break;
            case AbstractField::MULTIPLE_T:
                if ($field->getPlaceholder() !== '') {
                    $option['placeholder'] = $field->getPlaceholder();
                }
                $option["choices"] = $this->getChoices($field);
                $option["multiple"] = true;
                $option["expanded"] = $field->isExpanded();

                if ($formOptions['forceExpanded']) {
                    $option["expanded"] = true;
                }
                if ($field->isRequired() === false) {
                    $option['placeholder'] = 'none';
                }
                break;
            case AbstractField::DOCUMENTS_T:
                $option['multiple'] = true;
                $option['mapped'] = false;
                $mimeTypes = [
                    'application/pdf',
                    'application/x-pdf',
                    'image/jpeg',
                    'image/png',
                    'image/gif',
                ];
                if (!empty($field->getDefaultValues())) {
                    $mimeTypes = explode(',', $field->getDefaultValues());
                    $mimeTypes = array_map('trim', $mimeTypes);
                }
                $option['constraints'][] = new All([
                    'constraints' => [
                        new File([
                            'maxSize' => '10m',
                            'mimeTypes' => $mimeTypes
                        ])
                    ]
                ]);
                break;
            case AbstractField::COUNTRY_T:
                $option["expanded"] = $field->isExpanded();
                if ($field->getPlaceholder() !== '') {
                    $option['placeholder'] = $field->getPlaceholder();
                }
                if (!empty($field->getDefaultValues())) {
                    $countries = explode(',', $field->getDefaultValues());
                    $countries = array_map('trim', $countries);
                    $option['preferred_choices'] = $countries;
                }
                break;
            case AbstractField::EMAIL_T:
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

    /**
     * @param CustomFormField $field
     * @return array
     */
    protected function getChoices(CustomFormField $field): array
    {
        $choices = explode(',', $field->getDefaultValues() ?? '');
        $choices = array_map('trim', $choices);
        return array_combine(array_values($choices), array_values($choices));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'recaptcha_name' => Recaptcha::FORM_NAME,
            'forceExpanded' => false,
            'csrf_protection' => false,
        ]);

        $resolver->setRequired('customForm');

        $resolver->setAllowedTypes('customForm', [CustomForm::class]);
        $resolver->setAllowedTypes('forceExpanded', ['boolean']);
        $resolver->setAllowedTypes('recaptcha_name', ['string']);
    }

    /**
     * @return string
     */
    public function getBlockPrefix(): string
    {
        return 'custom_form_public';
    }
}
