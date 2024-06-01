<?php

declare(strict_types=1);

namespace RZ\Roadiz\EntityGenerator\Attribute;

class AttributeGenerator
{
    protected string $className;
    /**
     * @var array<string|int, string|int|array>
     */
    protected array $parameters;

    /**
     * @param string $className
     * @param array<string|int, string|int|array|null> $parameters
     */
    public function __construct(string $className, array $parameters = [])
    {
        $this->className = $className;
        $this->parameters = $parameters;
    }

    public static function wrapString(string $string): string
    {
        return sprintf('"%s"', str_replace('"', '\\"', $string));
    }

    public function generate(int $currentIndentation = 0): string
    {
        $formattedParams = [];
        if (count($this->parameters) > 3) {
            foreach ($this->parameters as $name => $parameter) {
                if (empty($parameter)) {
                    continue;
                }
                $formattedParams[] = $this->formatProperties($name, $parameter, $currentIndentation);
            }
            return
                str_repeat(' ', $currentIndentation) .
                $this->className .
                sprintf(
                    '(%s%s%s)',
                    PHP_EOL,
                    implode(',' . PHP_EOL, array_filter($formattedParams)),
                    PHP_EOL . str_repeat(' ', $currentIndentation),
                );
        } elseif (count($this->parameters) > 0) {
            foreach ($this->parameters as $name => $parameter) {
                if (empty($parameter)) {
                    continue;
                }
                $formattedParams[] = $this->formatProperties($name, $parameter, -4);
            }
            return
                str_repeat(' ', $currentIndentation) .
                $this->className .
                sprintf(
                    '(%s)',
                    implode(', ', array_filter($formattedParams))
                );
        } else {
            return str_repeat(' ', $currentIndentation) . $this->className;
        }
    }

    /**
     * @param string $name
     * @param array<string, mixed> $parameter
     * @param int $currentIndentation
     * @return string
     * @throws \JsonException
     */
    protected function formatArrayObject(string $name, array $parameter, int $currentIndentation = 0): string
    {
        $encodedParameterContent = [];
        foreach ($parameter as $key => $value) {
            if (is_string($key)) {
                $encodedParameterContent[] = sprintf(
                    '%s => %s',
                    self::wrapString($key),
                    \json_encode($value, \JSON_THROW_ON_ERROR)
                );
            }
        }
        return sprintf(
            '%s%s: %s',
            str_repeat(' ', $currentIndentation + 4),
            $name,
            '[' . implode(', ', $encodedParameterContent) . ']'
        );
    }

    protected function formatProperties(string|int $name, mixed $parameter, int $currentIndentation = 0): ?string
    {
        if (empty($parameter)) {
            return null;
        }
        if (is_string($name) && \is_array($parameter)) {
            return $this->formatArrayObject($name, $parameter, $currentIndentation);
        }
        if (is_string($name) && !empty($name)) {
            return sprintf(
                '%s%s: %s',
                str_repeat(' ', $currentIndentation + 4),
                $name,
                $parameter
            );
        }
        return sprintf(
            '%s%s',
            str_repeat(' ', $currentIndentation + 4),
            $parameter
        );
    }
}
