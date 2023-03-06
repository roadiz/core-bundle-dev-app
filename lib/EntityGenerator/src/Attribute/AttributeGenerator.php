<?php

declare(strict_types=1);

namespace RZ\Roadiz\EntityGenerator\Attribute;

class AttributeGenerator
{
    protected string $className;
    /**
     * @var array<string|int, string|int>
     */
    protected array $parameters;

    /**
     * @param string $className
     * @param int[]|string[] $parameters
     */
    public function __construct(string $className, array $parameters = [])
    {
        $this->className = $className;
        $this->parameters = $parameters;
    }

    public static function wrapString(string $string): string
    {
        return sprintf('"%s"', $string);
    }

    public function generate(int $currentIndentation = 0): string
    {
        $formattedParams = [];
        if (count($this->parameters) > 3) {
            foreach ($this->parameters as $name => $parameter) {
                if (is_string($name) && !empty($name)) {
                    $formattedParams[] = sprintf(
                        '%s%s: %s',
                        str_repeat(' ', $currentIndentation + 4),
                        $name,
                        $parameter
                    );
                } else {
                    $formattedParams[] = sprintf(
                        '%s%s',
                        str_repeat(' ', $currentIndentation + 4),
                        $parameter
                    );
                }
            }
            return
                str_repeat(' ', $currentIndentation) .
                $this->className .
                sprintf(
                    '(%s%s%s)',
                    PHP_EOL,
                    implode(',' . PHP_EOL, $formattedParams),
                    PHP_EOL . str_repeat(' ', $currentIndentation),
                );
        } elseif (count($this->parameters) > 0) {
            foreach ($this->parameters as $name => $parameter) {
                if (is_string($name) && !empty($name)) {
                    $formattedParams[] = sprintf('%s: %s', $name, $parameter);
                } else {
                    $formattedParams[] = $parameter;
                }
            }
            return
                str_repeat(' ', $currentIndentation) .
                $this->className .
                sprintf(
                    '(%s)',
                    implode(', ', $formattedParams)
                );
        } else {
            return str_repeat(' ', $currentIndentation) . $this->className;
        }
    }
}
