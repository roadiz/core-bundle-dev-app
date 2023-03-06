<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\Constraint;

use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Configuration\CollectionFieldConfiguration;
use RZ\Roadiz\CoreBundle\Configuration\JoinNodeTypeFieldConfiguration;
use RZ\Roadiz\CoreBundle\Configuration\ProviderFieldConfiguration;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField as NodeTypeFieldEntity;
use RZ\Roadiz\CoreBundle\Explorer\AbstractExplorerProvider;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class NodeTypeFieldValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if ($value instanceof NodeTypeFieldEntity) {
            if ($value->isMarkdown()) {
                $this->validateMarkdownOptions($value);
            }
            if ($value->isManyToMany() || $value->isManyToOne()) {
                $this->validateJoinTypes($value, $constraint);
            }
            if ($value->isMultiProvider() || $value->isSingleProvider()) {
                $this->validateProviderTypes($value, $constraint);
            }
            if ($value->isCollection()) {
                $this->validateCollectionTypes($value, $constraint);
            }
        } else {
            $this->context->buildViolation('Value is not a valid NodeTypeField.')->addViolation();
        }
    }

    /**
     * @param NodeTypeFieldEntity $value
     * @param Constraint $constraint
     */
    protected function validateJoinTypes(NodeTypeFieldEntity $value, Constraint $constraint): void
    {
        try {
            $defaultValuesParsed = Yaml::parse($value->getDefaultValues() ?? '');
            if (null === $defaultValuesParsed) {
                $this->context->buildViolation('default_values_should_not_be_empty_for_this_type')->atPath('defaultValues')->addViolation();
            } elseif (!is_array($defaultValuesParsed)) {
                $this->context->buildViolation('default_values_should_be_a_yaml_configuration_for_this_type')->atPath('defaultValues')->addViolation();
            } else {
                $configs = [
                    $defaultValuesParsed,
                ];
                $processor = new Processor();
                $joinConfig = new JoinNodeTypeFieldConfiguration();
                $configuration = $processor->processConfiguration($joinConfig, $configs);

                if (!class_exists($configuration['classname'])) {
                    $this->context->buildViolation('classname_%classname%_does_not_exist')
                        ->setParameter('%classname%', $configuration['classname'])
                        ->atPath('classname')
                        ->addViolation();
                    return;
                }

                $reflection = new \ReflectionClass($configuration['classname']);
                if (!$reflection->implementsInterface(PersistableInterface::class)) {
                    $this->context->buildViolation('classname_%classname%_must_extend_abstract_entity_class')
                        ->setParameter('%classname%', $configuration['classname'])
                        ->atPath('classname')
                        ->addViolation();
                }

                if (!$reflection->hasMethod($configuration['displayable'])) {
                    $this->context->buildViolation('classname_%classname%_does_not_declare_%method%_method')
                        ->setParameter('%classname%', $configuration['classname'])
                        ->setParameter('%method%', $configuration['displayable'])
                        ->atPath('displayable')
                        ->addViolation();
                }

                if (!empty($configuration['alt_displayable'])) {
                    if (!$reflection->hasMethod($configuration['alt_displayable'])) {
                        $this->context->buildViolation('classname_%classname%_does_not_declare_%method%_method')
                            ->setParameter('%classname%', $configuration['classname'])
                            ->setParameter('%method%', $configuration['alt_displayable'])
                            ->atPath('alt_displayable')
                            ->addViolation();
                    }
                }

                if (!empty($configuration['thumbnail'])) {
                    if (!$reflection->hasMethod($configuration['thumbnail'])) {
                        $this->context->buildViolation('classname_%classname%_does_not_declare_%method%_method')
                            ->setParameter('%classname%', $configuration['classname'])
                            ->setParameter('%method%', $configuration['thumbnail'])
                            ->atPath('thumbnail')
                            ->addViolation();
                    }
                }
            }
        } catch (ParseException $e) {
            $this->context->buildViolation($e->getMessage())->atPath('defaultValues')->addViolation();
        } catch (\RuntimeException $e) {
            $this->context->buildViolation($e->getMessage())->atPath('defaultValues')->addViolation();
        }
    }

    /**
     * @param NodeTypeFieldEntity $value
     * @param Constraint $constraint
     *
     * @throws \ReflectionException
     */
    protected function validateProviderTypes(NodeTypeFieldEntity $value, Constraint $constraint): void
    {
        try {
            if (null === $value->getDefaultValues()) {
                $this->context->buildViolation('default_values_should_not_be_empty_for_this_type')->atPath('defaultValues')->addViolation();
            } else {
                $defaultValuesParsed = Yaml::parse($value->getDefaultValues());
                if (null === $defaultValuesParsed) {
                    $this->context->buildViolation('default_values_should_not_be_empty_for_this_type')->atPath('defaultValues')->addViolation();
                } elseif (!is_array($defaultValuesParsed)) {
                    $this->context->buildViolation('default_values_should_be_a_yaml_configuration_for_this_type')->atPath('defaultValues')->addViolation();
                } else {
                    $configs = [
                        $defaultValuesParsed,
                    ];
                    $processor = new Processor();
                    $providerConfig = new ProviderFieldConfiguration();
                    $configuration = $processor->processConfiguration($providerConfig, $configs);

                    if (!class_exists($configuration['classname'])) {
                        $this->context->buildViolation('classname_%classname%_does_not_exist')
                            ->setParameter('%classname%', $configuration['classname'])
                            ->atPath('defaultValues')
                            ->addViolation();
                        return;
                    }

                    $reflection = new \ReflectionClass($configuration['classname']);
                    if (!$reflection->isSubclassOf(AbstractExplorerProvider::class)) {
                        $this->context->buildViolation('classname_%classname%_must_extend_abstract_explorer_provider_class')
                            ->setParameter('%classname%', $configuration['classname'])
                            ->atPath('defaultValues')
                            ->addViolation();
                    }
                }
            }
        } catch (ParseException $e) {
            $this->context->buildViolation($e->getMessage())->atPath('defaultValues')->addViolation();
        } catch (\RuntimeException $e) {
            $this->context->buildViolation($e->getMessage())->atPath('defaultValues')->addViolation();
        }
    }

    /**
     * @param NodeTypeFieldEntity $value
     * @param Constraint $constraint
     */
    protected function validateCollectionTypes(NodeTypeFieldEntity $value, Constraint $constraint): void
    {
        try {
            $defaultValuesParsed = Yaml::parse($value->getDefaultValues() ?? '');
            if (null === $defaultValuesParsed) {
                $this->context->buildViolation('default_values_should_not_be_empty_for_this_type')->atPath('defaultValues')->addViolation();
            } elseif (!is_array($defaultValuesParsed)) {
                $this->context->buildViolation('default_values_should_be_a_yaml_configuration_for_this_type')->atPath('defaultValues')->addViolation();
            } else {
                $configs = [
                    $defaultValuesParsed,
                ];
                $processor = new Processor();
                $providerConfig = new CollectionFieldConfiguration();
                $configuration = $processor->processConfiguration($providerConfig, $configs);

                if (!class_exists($configuration['entry_type'])) {
                    $this->context->buildViolation('classname_%classname%_does_not_exist')
                        ->setParameter('%classname%', $configuration['entry_type'])
                        ->atPath('defaultValues')
                        ->addViolation();
                    return;
                }

                $reflection = new \ReflectionClass($configuration['entry_type']);
                if (!$reflection->isSubclassOf(AbstractType::class)) {
                    $this->context->buildViolation('classname_%classname%_must_extend_abstract_type_class')
                        ->setParameter('%classname%', $configuration['entry_type'])
                        ->atPath('defaultValues')
                        ->addViolation();
                }
            }
        } catch (ParseException $e) {
            $this->context->buildViolation($e->getMessage())->atPath('defaultValues')->addViolation();
        } catch (\RuntimeException $e) {
            $this->context->buildViolation($e->getMessage())->atPath('defaultValues')->addViolation();
        }
    }

    /**
     * @param NodeTypeFieldEntity $value
     */
    protected function validateMarkdownOptions(NodeTypeFieldEntity $value): void
    {
        try {
            $options = Yaml::parse($value->getDefaultValues() ?? '');
            if (null !== $options && !is_array($options)) {
                $this->context
                    ->buildViolation('Markdown options must be an array.')
                    ->atPath('defaultValues')
                    ->addViolation();
            }
        } catch (ParseException $e) {
            $this->context->buildViolation($e->getMessage())->atPath('defaultValues')->addViolation();
        }
    }
}
