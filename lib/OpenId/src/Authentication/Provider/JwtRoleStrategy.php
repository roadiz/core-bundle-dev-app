<?php

declare(strict_types=1);

namespace RZ\Roadiz\OpenId\Authentication\Provider;

interface JwtRoleStrategy
{
    public function supports(): bool;
    public function getRoles(): ?array;
}
