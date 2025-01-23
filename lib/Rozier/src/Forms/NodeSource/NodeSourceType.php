<?php

declare(strict_types=1);

namespace Themes\Rozier\Forms\NodeSource;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\AbstractEntities\AbstractField;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Form\ColorType;
use RZ\Roadiz\CoreBundle\Form\CssType;
use RZ\Roadiz\CoreBundle\Form\EnumerationType;
use RZ\Roadiz\CoreBundle\Form\JsonType;
use RZ\Roadiz\CoreBundle\Form\MarkdownType;
use RZ\Roadiz\CoreBundle\Form\MultipleEnumerationType;
use RZ\Roadiz\CoreBundle\Form\YamlType;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeTypeFieldVoter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
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
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Yaml\Yaml;
use Themes\Rozier\Forms\GeoJsonType;
use Themes\Rozier\Forms\NodeTreeType;

final class NodeSourceType extends AbstractType
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly Security $security,
    ) {
    }

    /**
     * @throws \ReflectionException
     */
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

    public function getBlockPrefix(): string
    {
        return 'source';
    }

    /**
     * @return array<NodeTypeField>
     */
    private function getFieldsForSource(NodesSources $source, NodeType $nodeType): array
    {
        $fields = $nodeType->getFields()->filter(function (NodeTypeField $field) {
            return $field->isVisible();
        });

        if (!$this->needsUniversalFields($source)) {
            $fields = $fields->filter(function (NodeTypeField $field) {
                return !$field->isUniversal();
            });
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

        $sourceCount = $this->managerRegistry->getRepository(NodesSources::class)
                                     ->setDisplayingAllNodesStatuses(true)
                                     ->setDisplayingNotPublishedNodes(true)
                                     ->countBy([
                                         'node' => $source->getNode(),
                                         'translation' => $defaultTranslation,
                                     ]);

        return 1 === $sourceCount;
    }

    /**
     * Returns a Symfony Form type according to a node-type field.
     *
     * @return class-string<AbstractType>
     */
    public static function getFormTypeFromFieldType(AbstractField $field): string
    {
        switch ($field->getType()) {
            case AbstractField::COLOUR_T:
                return ColorType::class;

            case AbstractField::GEOTAG_T:
            case AbstractField::MULTI_GEOTAG_T:
                return GeoJsonType::class;

            case AbstractField::STRING_T:
                return TextType::class;

            case AbstractField::DATETIME_T:
                return DateTimeType::class;

            case AbstractField::DATE_T:
                return DateType::class;

            case AbstractField::RICHTEXT_T:
            case AbstractField::TEXT_T:
                return TextareaType::class;

            case AbstractField::MARKDOWN_T:
                return MarkdownType::class;

            case AbstractField::BOOLEAN_T:
                return CheckboxType::class;

            case AbstractField::INTEGER_T:
                return IntegerType::class;

            case AbstractField::DECIMAL_T:
                return NumberType::class;

            case AbstractField::EMAIL_T:
                return EmailType::class;

            case AbstractField::RADIO_GROUP_T:
            case AbstractField::ENUM_T:
                return EnumerationType::class;

            case AbstractField::MULTIPLE_T:
            case AbstractField::CHECK_GROUP_T:
                return MultipleEnumerationType::class;

            case AbstractField::DOCUMENTS_T:
                return NodeSourceDocumentType::class;

            case AbstractField::NODES_T:
                return NodeSourceNodeType::class;

            case AbstractField::CHILDREN_T:
                return NodeTreeType::class;

            case AbstractField::CUSTOM_FORMS_T:
                return NodeSourceCustomFormType::class;

            case AbstractField::JSON_T:
                return JsonType::class;

            case AbstractField::CSS_T:
                return CssType::class;

            case AbstractField::COUNTRY_T:
                return CountryType::class;

            case AbstractField::YAML_T:
                return YamlType::class;

            case AbstractField::PASSWORD_T:
                return PasswordType::class;

            case AbstractField::MANY_TO_MANY_T:
            case AbstractField::MANY_TO_ONE_T:
                return NodeSourceJoinType::class;

            case AbstractField::SINGLE_PROVIDER_T:
            case AbstractField::MULTI_PROVIDER_T:
                return NodeSourceProviderType::class;

            case AbstractField::COLLECTION_T:
                return NodeSourceCollectionType::class;
        }

        return TextType::class;
    }

    /**
     * Returns an option array for creating a Symfony Form
     * according to a node-type field.
     *
     * @return array
     *
     * @throws \ReflectionException
     */
    public function getFormOptionsFromFieldType(NodesSources $nodeSource, NodeTypeField $field, array &$formOptions)
    {
        $options = $this->getDefaultOptions($nodeSource, $field, $formOptions);

        switch ($field->getType()) {
            case AbstractField::ENUM_T:
            case AbstractField::MULTIPLE_T:
                $options = array_merge_recursive($options, [
                    'nodeTypeField' => $field,
                ]);
                break;
            case AbstractField::MANY_TO_ONE_T:
            case AbstractField::MANY_TO_MANY_T:
                $options = array_merge_recursive($options, [
                    'attr' => [
                        'data-nodetypefield' => $field->getId(),
                    ],
                ]);
                break;
            case AbstractField::NODES_T:
                $options = array_merge_recursive($options, [
                    'attr' => [
                        'data-nodetypes' => json_encode(explode(
                            ',',
                            $field->getDefaultValues() ?? ''
                        )),
                    ],
                ]);
                break;
            case AbstractField::DATETIME_T:
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
            case AbstractField::DATE_T:
                $options = array_merge_recursive($options, [
                    'widget' => 'single_text',
                    'format' => 'yyyy-MM-dd',
                    'attr' => [
                        'class' => 'rz-date-field',
                    ],
                    'placeholder' => '',
                ]);
                break;
            case AbstractField::DECIMAL_T:
            case AbstractField::INTEGER_T:
                $options = array_merge_recursive($options, [
                    'constraints' => [
                        new Type('numeric'),
                    ],
                ]);
                break;
            case AbstractField::EMAIL_T:
                $options = array_merge_recursive($options, [
                    'constraints' => [
                        new Email(),
                        new Length([
                            'max' => 255,
                        ]),
                    ],
                ]);
                break;
            case AbstractField::STRING_T:
                $options = array_merge_recursive($options, [
                    'constraints' => [
                        new Length([
                            'max' => 255,
                        ]),
                    ],
                ]);
                break;
            case AbstractField::GEOTAG_T:
                $options = array_merge_recursive($options, [
                    'attr' => [
                        'class' => 'rz-geotag-field',
                    ],
                ]);
                break;
            case AbstractField::MULTI_GEOTAG_T:
                $options = array_merge_recursive($options, [
                    'attr' => [
                        'class' => 'rz-multi-geotag-field',
                    ],
                ]);
                break;
            case AbstractField::MARKDOWN_T:
                $additionalOptions = Yaml::parse($field->getDefaultValues() ?? '[]');
                $options = array_merge_recursive($options, [
                    'attr' => [
                        'class' => 'markdown_textarea',
                    ],
                ], $additionalOptions);
                break;
            case AbstractField::CHILDREN_T:
                $options = array_merge_recursive($options, [
                    'nodeSource' => $nodeSource,
                    'nodeTypeField' => $field,
                ]);
                break;
            case AbstractField::COUNTRY_T:
                $options = array_merge_recursive($options, [
                    'expanded' => $field->isExpanded(),
                ]);
                if ('' !== $field->getPlaceholder()) {
                    $options['placeholder'] = $field->getPlaceholder();
                }
                if ('' !== $field->getDefaultValues()) {
                    $countries = explode(',', $field->getDefaultValues() ?? '');
                    $countries = array_map('trim', $countries);
                    $options = array_merge_recursive($options, [
                        'preferred_choices' => $countries,
                    ]);
                }
                break;
            case AbstractField::COLLECTION_T:
                $configuration = Yaml::parse($field->getDefaultValues() ?? '');
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
            && AbstractField::MANY_TO_ONE_T !== $field->getType()
            && AbstractField::MANY_TO_MANY_T !== $field->getType()
        ) {
            $options['mapped'] = false;
        }

        if (
            in_array($field->getType(), [
                AbstractField::MANY_TO_ONE_T,
                AbstractField::MANY_TO_MANY_T,
                AbstractField::DOCUMENTS_T,
                AbstractField::NODES_T,
                AbstractField::CUSTOM_FORMS_T,
                AbstractField::MULTI_PROVIDER_T,
                AbstractField::SINGLE_PROVIDER_T,
            ])
        ) {
            $options['nodeTypeField'] = $field;
            $options['nodeSource'] = $nodeSource;
            unset($options['attr']['dir']);
        }

        if (AbstractField::CHILDREN_T === $field->getType()) {
            unset($options['attr']['dir']);
        }

        return $options;
    }
}
