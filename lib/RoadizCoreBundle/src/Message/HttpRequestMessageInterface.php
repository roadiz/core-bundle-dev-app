<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Message;

interface HttpRequestMessageInterface
{
    public function getMethod(): string;

    public function getUri(): string;

    public function getOptions(): array;
}
