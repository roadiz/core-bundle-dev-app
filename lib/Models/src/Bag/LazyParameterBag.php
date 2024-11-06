<?php

declare(strict_types=1);

namespace RZ\Roadiz\Bag;

use Symfony\Component\HttpFoundation\ParameterBag;

abstract class LazyParameterBag extends ParameterBag
{
    protected bool $ready;

    abstract protected function populateParameters(): void;

    public function __construct()
    {
        parent::__construct();
        $this->ready = false;
    }

    /**
     * @param mixed|null $default
     *
     * @return mixed|null
     */
    public function get(string $key, $default = null): mixed
    {
        if (!$this->ready) {
            $this->populateParameters();
        }

        return parent::get($key, $default);
    }

    public function all(?string $key = null): array
    {
        if (!$this->ready) {
            $this->populateParameters();
        }

        return parent::all();
    }

    public function has(string $key): bool
    {
        if (!$this->ready) {
            $this->populateParameters();
        }

        return parent::has($key);
    }

    public function keys(): array
    {
        if (!$this->ready) {
            $this->populateParameters();
        }

        return parent::keys();
    }

    #[\ReturnTypeWillChange]
    public function getIterator(): \ArrayIterator
    {
        if (!$this->ready) {
            $this->populateParameters();
        }

        return parent::getIterator();
    }

    #[\ReturnTypeWillChange]
    public function count(): int
    {
        if (!$this->ready) {
            $this->populateParameters();
        }

        return parent::count();
    }

    /**
     * @param null  $default
     * @param array $options
     */
    public function filter(string $key, $default = null, int $filter = \FILTER_DEFAULT, $options = []): mixed
    {
        if (!$this->ready) {
            $this->populateParameters();
        }

        return parent::filter($key, $default, $filter, $options);
    }

    public function reset(): void
    {
        $this->parameters = [];
        $this->ready = false;
    }
}
