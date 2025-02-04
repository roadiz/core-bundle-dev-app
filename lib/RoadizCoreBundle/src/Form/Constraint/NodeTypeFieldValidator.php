<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\Constraint;

use Doctrine\Persistence\ManagerRegistry;
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

/**
 * @deprecated nodeTypes will be static in future Roadiz versions
 */
class NodeTypeFieldValidator extends ConstraintValidator
{
    public function __construct(
        private readonly ManagerRegistry $registry,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$value instanceof NodeTypeFieldEntity) {
            $this->context->buildViolation('Value is not a valid NodeTypeField.')->addViolation();

            return;
        }

        $existingNodeTypeFieldsByName = $this->registry->getRepository(NodeTypeFieldEntity::class)->findBy([
            'name' => $value->getName(),
        ]);
        foreach ($existingNodeTypeFieldsByName as $item) {
            if ($item->getId() === $value->getId()) {
                continue;
            }
            if ($item->getDoctrineType() !== $value->getDoctrineType()) {
                $this->context->buildViolation('field_with_same_name_already_exists_but_with_different_doctrine_type')
                    ->setParameter('%name%', $item->getName())
                    ->setParameter('%nodeTypeName%', $item->getNodeTypeName())
                    ->setParameter('%type%', $item->getDoctrineType())
                    ->atPath('name')
                    ->addViolation();
            }
        }

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
    }

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

    protected function validateMarkdownOptions(NodeTypeFieldEntity $value): void
    {
        try {
            $options = $value->getDefaultValuesAsArray();
            if (0 === count($options)) {
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
