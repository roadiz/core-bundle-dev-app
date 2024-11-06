<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Routing;

class NodePathInfo
{
    protected ?string $path = null;
    protected array $parameters = [];
    protected bool $isComplete = false;
    protected bool $containsScheme = false;

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): NodePathInfo
    {
        $this->path = $path;

        return $this;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function setParameters(array $parameters): NodePathInfo
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function isComplete(): bool
    {
        return $this->isComplete;
    }

    public function setComplete(bool $isComplete): NodePathInfo
    {
        $this->isComplete = $isComplete;

        return $this;
    }

    public function containsScheme(): bool
    {
        return $this->containsScheme;
    }

    public function setContainsScheme(bool $containsScheme): NodePathInfo
    {
        $this->containsScheme = $containsScheme;

        return $this;
    }

    /**
     * @deprecated Use __serialize
     */
    public function serialize(): string
    {
        $json = \json_encode([
            'path' => $this->getPath(),
            'parameters' => $this->getParameters(),
            'is_complete' => $this->isComplete(),
            'contains_scheme' => $this->containsScheme(),
        ]);
        if (false === $json) {
            throw new \RuntimeException('Unable to serialize NodePathInfo');
        }

        return $json;
    }

    public function __serialize(): array
    {
        return [
            'path' => $this->getPath(),
            'parameters' => $this->getParameters(),
            'is_complete' => $this->isComplete(),
            'contains_scheme' => $this->containsScheme(),
        ];
    }

    /**
     * @deprecated Use __unserialize
     */
    public function unserialize(string $serialized): void
    {
        $data = \json_decode($serialized, true);
        $this->setComplete($data['is_complete']);
        $this->setParameters($data['parameters']);
        $this->setPath($data['path']);
        $this->setContainsScheme($data['contains_scheme']);
    }

    public function __unserialize(array $data): void
    {
        $this->setComplete($data['is_complete']);
        $this->setParameters($data['parameters']);
        $this->setPath($data['path']);
        $this->setContainsScheme($data['contains_scheme']);
    }
}
