<?php

declare(strict_types=1);

namespace RZ\Roadiz\EntityGenerator\Field;

use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;
use Symfony\Component\Yaml\Yaml;

abstract class AbstractConfigurableFieldGenerator extends AbstractFieldGenerator
{
    protected array $configuration;

    public function __construct(NodeTypeFieldInterface $field, array $options = [])
    {
        parent::__construct($field, $options);

        if (empty($this->field->getDefaultValues())) {
            throw new \LogicException('Default values must be a valid YAML for ' . static::class);
        }
        $conf = Yaml::parse($this->field->getDefaultValues());
        if (!is_array($conf)) {
            throw new \LogicException('YAML for ' . static::class . ' must be an associative array');
        }
        $this->configuration = $conf;
    }

    /**
     * Ensure configured classname has a starting backslash.
     *
     * @return string
     */
    protected function getFullyQualifiedClassName(): string
    {
        return '\\' . trim($this->configuration['classname'], '\\');
    }
}
