<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Security\Authorization\Voter;

use RZ\Roadiz\CoreBundle\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class UserVoter extends Voter
{
    public const string EDIT = 'USER_EDIT';
    public const string EDIT_DETAIL = 'USER_EDIT_DETAIL';

    public function __construct(
        private readonly Security $security,
    ) {
    }

    #[\Override]
    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::EDIT, self::EDIT_DETAIL], true)
            && $subject instanceof UserInterface;
    }

    #[\Override]
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        // if the subject is not a User instance, do not grant access
        if (!$subject instanceof UserInterface) {
            return false;
        }

        if ($this->security->isGranted('ROLE_SUPERADMIN')) {
            return true;
        }

        // If subject User is a super admin, deny access to all actions unless the user is also a super admin.
        if ($this->isSubjectUserAdmin($subject)) {
            return false;
        }

        return match ($attribute) {
            self::EDIT => $this->security->isGranted('ROLE_ACCESS_USERS') || $this->isOwnUser($subject),
            self::EDIT_DETAIL => ($this->security->isGranted('ROLE_ACCESS_USERS') || $this->isOwnUser($subject))
                && $this->security->isGranted('ROLE_ACCESS_USERS_DETAIL'),
            default => false,
        };
    }

    protected function isSubjectUserAdmin(UserInterface $subject): bool
    {
        return \in_array('ROLE_SUPERADMIN', $subject->getRoles(), true);
    }

    protected function isOwnUser(UserInterface $subject): bool
    {
        return $subject->getUserIdentifier() === $this->security->getUser()?->getUserIdentifier();
    }
}
