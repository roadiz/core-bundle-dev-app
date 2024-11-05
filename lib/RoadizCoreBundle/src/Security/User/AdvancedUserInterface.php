<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Security\User;

interface AdvancedUserInterface
{
    public function isAccountNonExpired(): bool;

    public function isAccountNonLocked(): bool;

    public function isCredentialsNonExpired(): bool;

    public function isEnabled(): bool;
}
