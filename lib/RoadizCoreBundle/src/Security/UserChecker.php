<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Security;

use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Security\Exception\UserCredentialsExpiredException;
use RZ\Roadiz\CoreBundle\Security\Exception\UserExpiredException;
use RZ\Roadiz\CoreBundle\Security\Exception\UserLockedException;
use RZ\Roadiz\CoreBundle\Security\Exception\UserNotEnabledException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class UserChecker implements UserCheckerInterface
{
    #[\Override]
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if (!$user->isEnabled()) {
            // the message passed to this exception is meant to be displayed to the user
            throw new UserNotEnabledException('Your user account is not enabled. Contact an administrator.');
        }
    }

    #[\Override]
    public function checkPostAuth(UserInterface $user, ?TokenInterface $token = null): void
    {
        if (!$user instanceof User) {
            return;
        }

        if (!$user->isAccountNonLocked()) {
            // the message passed to this exception is meant to be displayed to the user
            throw new UserLockedException('Your user account is locked. Contact an administrator.');
        }
        if (!$user->isCredentialsNonExpired()) {
            // the message passed to this exception is meant to be displayed to the user
            throw new UserCredentialsExpiredException('Your credentials have expired. Please request a new password.');
        }
        if (!$user->isAccountNonExpired()) {
            // the message passed to this exception is meant to be displayed to the user
            throw new UserExpiredException('Your account has expired. Contact an administrator.');
        }
    }
}
