<?php

declare(strict_types=1);

namespace RZ\Roadiz\OpenId\Authentication\Provider;

final class ChainJwtRoleStrategy implements JwtRoleStrategy
{
    /**
     * @param array<JwtRoleStrategy> $strategies
     */
    public function __construct(private readonly array $strategies)
    {
        foreach ($this->strategies as $strategy) {
            if (!($strategy instanceof JwtRoleStrategy)) {
                throw new \InvalidArgumentException('Strategy must implement '.JwtRoleStrategy::class);
            }
        }
    }

    public function supports(): bool
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->supports()) {
                return true;
            }
        }

        return false;
    }

    public function getRoles(): ?array
    {
        $roles = [];
        foreach ($this->strategies as $strategy) {
            if ($strategy->supports()) {
                $roles = array_merge($roles, $strategy->getRoles() ?? []);
            }
        }

        return !empty($roles) ? array_unique($roles) : null;
    }
}
