<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Message;

final readonly class HttpRequestMessage implements AsyncMessage, HttpRequestMessageInterface
{
    private array $options;

    public function __construct(
        private string $method,
        private string $uri,
        array $options = [],
    ) {
        $this->options = array_merge([
            'timeout' => 3,
        ], $options);
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUri(): string
    {
        return $this->uri;
    }
}
