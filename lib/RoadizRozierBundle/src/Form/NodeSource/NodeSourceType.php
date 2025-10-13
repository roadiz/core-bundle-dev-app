<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Form\NodeSource;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\AbstractField;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Enum\FieldType;
use RZ\Roadiz\CoreBundle\Form\CssType;
use RZ\Roadiz\CoreBundle\Form\EnumerationType;
use RZ\Roadiz\CoreBundle\Form\JsonType;
use RZ\Roadiz\CoreBundle\Form\MarkdownType;
use RZ\Roadiz\CoreBundle\Form\MultipleEnumerationType;
use RZ\Roadiz\CoreBundle\Form\YamlType;
use RZ\Roadiz\CoreBundle\Repository\AllStatusesNodesSourcesRepository;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeTypeFieldVoter;
use RZ\Roadiz\RozierBundle\Form\GeoJsonType;
use RZ\Roadiz\RozierBundle\Form\NodeTreeType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;

final class NodeSourceType extends AbstractType
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly Security $security,
        private readonly AllStatusesNodesSourcesRepository $allStatusesNodesSourcesRepository,
    ) {
    }

    /**
     * @throws \ReflectionException
     */
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $fields = $this->getFieldsForSource($builder->getData(), $options['nodeType']);

        if (true === $options['withTitle']) {
            $builder->add('base', NodeSourceBaseType::class, [
                'publishable' => $options['nodeType']->isPublishable(),
                'translation' => $builder->getData()->getTranslation(),
            ]);
        }

        foreach ($fields as $field) {
            if (!$this->security->isGranted(NodeTypeFieldVoter::VIEW, $field)) {
                continue;
            }
            if (true === $options['withVirtual'] || !$field->isVirtual()) {
                $builder->add(
                    $field->getVarName(),
                    self::getFormTypeFromFieldType($field),
                    $this->getFormOptionsFromFieldType($builder->getData(), $field, $options)
                );
            }
        }
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'property' => 'id',
            'withTitle' => true,
            'withVirtual' => true,
        ]);
        $resolver->setRequired([
            'class',
            'nodeType',
        ]);
        $resolver->setAllowedTypes('withTitle', 'boolean');
        $resolver->setAllowedTypes('withVirtual', 'boolean');
        $resolver->setAllowedTypes('nodeType', NodeType::class);
        $resolver->setAllowedTypes('class', 'string');
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'source';
    }

    /**
     * @return array<NodeTypeField>
     */
    private function getFieldsForSource(NodesSources $source, NodeType $nodeType): array
    {
        $fields = $nodeType->getFields()->filter(fn (NodeTypeField $field) => $field->isVisible());

        if (!$this->needsUniversalFields($source)) {
            $fields = $fields->filter(fn (NodeTypeField $field) => !$field->isUniversal());
        }

        return $fields->toArray();
    }

    private function needsUniversalFields(NodesSources $source): bool
    {
        return $source->getTranslation()->isDefaultTranslation() || !$this->hasDefaultTranslation($source);
    }

    private function hasDefaultTranslation(NodesSources $source): bool
    {
        /** @var Translation $defaultTranslation */
        $defaultTranslation = $this->managerRegistry->getRepository(Translation::class)
                                            ->findDefault();

        $sourceCount = $this->allStatusesNodesSourcesRepository->countBy([
            'node' => $source->getNode(),
            'translation' => $defaultTranslation,
        ]);

        return 1 === $sourceCount;
    }

    /**
     * @return class-string<AbstractType>
     */
    public static function getFormTypeFromFieldType(AbstractField $field): string
    {
        return match ($field->getType()) {
            FieldType::BOOLEAN_T => CheckboxType::class,
            FieldType::CHILDREN_T => NodeTreeType::class,
            FieldType::COLLECTION_T => NodeSourceCollectionType::class,
            FieldType::COLOUR_T => ColorType::class,
            FieldType::COUNTRY_T => CountryType::class,
            FieldType::CSS_T => CssType::class,
            FieldType::CUSTOM_FORMS_T => NodeSourceCustomFormType::class,
            FieldType::DATETIME_T => DateTimeType::class,
            FieldType::DATE_T => DateType::class,
            FieldType::DECIMAL_T => NumberType::class,
            FieldType::DOCUMENTS_T => NodeSourceDocumentType::class,
            FieldType::EMAIL_T => EmailType::class,
            FieldType::GEOTAG_T, FieldType::MULTI_GEOTAG_T => GeoJsonType::class,
            FieldType::INTEGER_T => IntegerType::class,
            FieldType::JSON_T => JsonType::class,
            FieldType::MANY_TO_MANY_T, FieldType::MANY_TO_ONE_T => NodeSourceJoinType::class,
            FieldType::MARKDOWN_T => MarkdownType::class,
            FieldType::MULTIPLE_T, FieldType::CHECK_GROUP_T => MultipleEnumerationType::class,
            FieldType::NODES_T => NodeSourceNodeType::class,
            FieldType::PASSWORD_T => PasswordType::class,
            FieldType::RADIO_GROUP_T, FieldType::ENUM_T => EnumerationType::class,
            FieldType::RICHTEXT_T, FieldType::TEXT_T => TextareaType::class,
            FieldType::SINGLE_PROVIDER_T, FieldType::MULTI_PROVIDER_T => NodeSourceProviderType::class,
            FieldType::YAML_T => YamlType::class,
            default => TextType::class,
        };
    }

    /**
     * Returns an option array for creating a Symfony Form
     * according to a node-type field.
     *
     * @throws \ReflectionException
     */
    public function getFormOptionsFromFieldType(NodesSources $nodeSource, NodeTypeField $field, array &$formOptions): array
    {
        $options = $this->getDefaultOptions($nodeSource, $field, $formOptions);

        switch ($field->getType()) {
            case FieldType::ENUM_T:
            case FieldType::MULTIPLE_T:
                $options = array_merge_recursive($options, [
                    'nodeTypeField' => $field,
                ]);
                break;
            case FieldType::MANY_TO_ONE_T:
            case FieldType::MANY_TO_MANY_T:
                $options = array_merge_recursive($options, [
                    '_locale' => $nodeSource->getTranslation()->getLocale(),
                    'attr' => [
                        'data-nodetypefield' => $field->getName(),
                        'data-nodetypename' => $field->getNodeTypeName(),
                    ],
                ]);
                break;
            case FieldType::DOCUMENTS_T:
            case FieldType::SINGLE_PROVIDER_T:
            case FieldType::MULTI_PROVIDER_T:
                $options = array_merge_recursive($options, [
                    '_locale' => $nodeSource->getTranslation()->getLocale(),
                ]);
                break;
            case FieldType::NODES_T:
                $options = array_merge_recursive($options, [
                    'attr' => [
                        'data-nodetypes' => json_encode(array_map('trim', $field->getDefaultValuesAsArray())),
                    ],
                    '_locale' => $nodeSource->getTranslation()->getLocale(),
                ]);
                break;
            case FieldType::DATETIME_T:
                $options = array_merge_recursive($options, [
                    'date_widget' => 'single_text',
                    'date_format' => 'yyyy-MM-dd',
                    'attr' => [
                        'class' => 'rz-datetime-field',
                    ],
                    'placeholder' => [
                        'hour' => 'hour',
                        'minute' => 'minute',
                    ],
                ]);
                break;
            case FieldType::DATE_T:
                $options = array_merge_recursive($options, [
                    'widget' => 'single_text',
                    'format' => 'yyyy-MM-dd',
                    'attr' => [
                        'class' => 'rz-date-field',
                    ],
                    'placeholder' => '',
                ]);
                break;
            case FieldType::DECIMAL_T:
            case FieldType::INTEGER_T:
                $options = array_merge_recursive($options, [
                    'constraints' => [
                        new Type('numeric'),
                    ],
                ]);
                break;
            case FieldType::EMAIL_T:
                $options = array_merge_recursive($options, [
                    'constraints' => [
                        new Email(),
                        new Length([
                            'max' => 255,
                        ]),
                    ],
                ]);
                break;
            case FieldType::STRING_T:
                $options = array_merge_recursive($options, [
                    'constraints' => [
                        new Length([
                            'max' => 255,
                        ]),
                    ],
                ]);
                break;
            case FieldType::GEOTAG_T:
                $options = array_merge_recursive($options, [
                    'attr' => [
                        'class' => 'rz-geotag-field',
                    ],
                ]);
                break;
            case FieldType::MULTI_GEOTAG_T:
                $options = array_merge_recursive($options, [
                    'attr' => [
                        'class' => 'rz-multi-geotag-field',
                    ],
                ]);
                break;
            case FieldType::MARKDOWN_T:
                $additionalOptions = $field->getDefaultValuesAsArray();
                $options = array_merge_recursive($options, [
                    'attr' => [
                        'class' => 'markdown_textarea',
                    ],
                    'locale' => $nodeSource->getTranslation()->getLocale(),
                ], $additionalOptions);
                break;
            case FieldType::CHILDREN_T:
                $options = array_merge_recursive($options, [
                    'nodeSource' => $nodeSource,
                    'nodeTypeField' => $field,
                ]);
                break;
            case FieldType::COUNTRY_T:
                $options = array_merge_recursive($options, [
                    'expanded' => $field->isExpanded(),
                ]);
                if ('' !== $field->getPlaceholder()) {
                    $options['placeholder'] = $field->getPlaceholder();
                }
                $defaultValuesAsArray = $field->getDefaultValuesAsArray();
                if (count($defaultValuesAsArray) > 0) {
                    $countries = $defaultValuesAsArray;
                    $countries = array_map('trim', $countries);
                    $options = array_merge_recursive($options, [
                        'preferred_choices' => $countries,
                    ]);
                }
                break;
            case FieldType::COLLECTION_T:
                $configuration = $field->getDefaultValuesAsArray();
                $collectionOptions = [
                    'allow_add' => true,
                    'allow_delete' => true,
                    'attr' => [
                        'class' => 'rz-collection-form-type',
                    ],
                    'entry_options' => [
                        'label' => false,
                    ],
                ];
                if (isset($configuration['entry_type'])) {
                    $reflectionClass = new \ReflectionClass($configuration['entry_type']);
                    if ($reflectionClass->isSubclassOf(AbstractType::class)) {
                        $collectionOptions['entry_type'] = $reflectionClass->getName();
                    }
                }
                $options = array_merge_recursive($options, $collectionOptions);
                break;
            case FieldType::COLOUR_T:
                $options = array_merge_recursive($options, [
                    'html5' => true,
                ]);
                break;
        }

        return $options;
    }

    /**
     * Get common options for your node-type field form components.
     */
    public function getDefaultOptions(NodesSources $nodeSource, NodeTypeField $field, array &$formOptions): array
    {
        $label = $field->getLabel();
        $devName = '{{ nodeSource.'.$field->getVarName().' }}';
        $options = [
            'label' => $label,
            'required' => false,
            'attr' => [
                'data-field-group' => (null !== $field->getGroupName() && '' != $field->getGroupName()) ?
                    $field->getGroupName() :
                    'default',
                'data-field-group-canonical' => (
                    null !== $field->getGroupNameCanonical()
                    && '' != $field->getGroupNameCanonical()
                ) ? $field->getGroupNameCanonical() : 'default',
                'data-dev-name' => $devName,
                'autocomplete' => 'off',
                'lang' => \mb_strtolower(str_replace('_', '-', $nodeSource->getTranslation()->getLocale())),
                'dir' => $nodeSource->getTranslation()->isRtl() ? 'rtl' : 'ltr',
            ],
        ];
        if ($field->isUniversal()) {
            $options['attr']['data-universal'] = true;
        }
        if ('' !== $field->getDescription()) {
            $options['help'] = $field->getDescription();
        }
        if ('' !== $field->getPlaceholder()) {
            $options['attr']['placeholder'] = $field->getPlaceholder();
        }
        if ($field->getMinLength() > 0) {
            $options['attr']['data-min-length'] = $field->getMinLength();
        }
        if ($field->getMaxLength() > 0) {
            $options['attr']['data-max-length'] = $field->getMaxLength();
        }
        if (
            $field->isVirtual()
            && FieldType::MANY_TO_ONE_T !== $field->getType()
            && FieldType::MANY_TO_MANY_T !== $field->getType()
        ) {
            $options['mapped'] = false;
        }

        if ($field->isRequired()) {
            if (in_array($field->getType(), [
                FieldType::DOCUMENTS_T,
                FieldType::NODES_T,
                FieldType::CUSTOM_FORMS_T,
                FieldType::MANY_TO_MANY_T,
            ])) {
                $options['constraints'] = [
                    new Count(min: 1),
                    new NotNull(),
                ];
            }

            if (FieldType::MANY_TO_ONE_T === $field->getType()) {
                $options['constraints'] = [
                    new NotBlank(),
                ];
            }
        }

        if (
            in_array($field->getType(), [
                FieldType::MANY_TO_ONE_T,
                FieldType::MANY_TO_MANY_T,
                FieldType::DOCUMENTS_T,
                FieldType::NODES_T,
                FieldType::CUSTOM_FORMS_T,
                FieldType::MULTI_PROVIDER_T,
                FieldType::SINGLE_PROVIDER_T,
            ])
        ) {
            $options['nodeTypeField'] = $field;
            $options['nodeSource'] = $nodeSource;
            unset($options['attr']['dir']);
        }

        if (FieldType::CHILDREN_T === $field->getType()) {
            unset($options['attr']['dir']);
        }

        return $options;
    }
}
