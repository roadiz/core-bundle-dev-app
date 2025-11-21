<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Security\Authorization\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class UserVoter extends Voter
{
    public const string EDIT = 'USER_EDIT';
    public const string EDIT_DETAIL = 'USER_EDIT_DETAIL';
    public const string VIEW_HISTORY = 'USER_VIEW_HISTORY';

    public function __construct(
        private readonly AccessDecisionManagerInterface $accessDecisionManager,
    ) {
    }

    #[\Override]
    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::EDIT, self::EDIT_DETAIL, self::VIEW_HISTORY], true)
            && $subject instanceof UserInterface;
    }

    #[\Override]
    public function supportsAttribute(string $attribute): bool
    {
        return in_array($attribute, [
            self::EDIT,
            self::EDIT_DETAIL,
            self::VIEW_HISTORY,
        ], true);
    }

    #[\Override]
    public function supportsType(string $subjectType): bool
    {
        return is_a($subjectType, UserInterface::class, true);
    }

    #[\Override]
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        if ($this->accessDecisionManager->decide($token, ['ROLE_SUPERADMIN'])) {
            $vote?->addReason('Token is granted with ROLE_SUPERADMIN.');

            return true;
        }

        // if the subject is not a User instance, do not grant access
        if (!$subject instanceof UserInterface) {
            $vote?->addReason('Subject is not a User instance.');

            return false;
        }

        // If subject User is a super admin, deny access to all actions unless the user is also a super admin.
        if ($this->isSubjectUserAdmin($subject)) {
            $vote?->addReason('Subject is a super admin, access denied unless you are also a super admin.');

            return false;
        }

        $isOwnUser = $this->isOwnUser($subject, $token);
        if ($isOwnUser) {
            $vote?->addReason('User is acting on its own account.');
        }

        return match ($attribute) {
            self::EDIT => $this->accessDecisionManager->decide($token, ['ROLE_ACCESS_USERS']) || $isOwnUser,
            self::VIEW_HISTORY => $this->canViewHistory($isOwnUser, $token, $vote),
            self::EDIT_DETAIL => $this->canEditDetail($isOwnUser, $token, $vote),
            default => false,
        };
    }

    protected function canEditDetail(bool $isOwnUser, TokenInterface $token, ?Vote $vote = null): bool
    {
        if (!$this->accessDecisionManager->decide($token, ['ROLE_ACCESS_USERS_DETAIL'])) {
            $vote?->addReason('Users must have ROLE_ACCESS_USERS_DETAIL to edit details, even if it is their own account.');

            return false;
        }

        return $this->accessDecisionManager->decide($token, ['ROLE_ACCESS_USERS']) || $isOwnUser;
    }

    protected function canViewHistory(bool $isOwnUser, TokenInterface $token, ?Vote $vote = null): bool
    {
        if ($isOwnUser) {
            $vote?->addReason('Users can see their own history.');

            return true;
        }

        return $this->accessDecisionManager->decide($token, ['ROLE_ACCESS_LOGS'])
            && $this->accessDecisionManager->decide($token, ['ROLE_ACCESS_USERS']);
    }

    protected function isSubjectUserAdmin(UserInterface $subject): bool
    {
        return \in_array('ROLE_SUPERADMIN', $subject->getRoles(), true);
    }

    protected function isOwnUser(UserInterface $subject, TokenInterface $token): bool
    {
        return $subject->getUserIdentifier() === $token->getUser()?->getUserIdentifier();
    }
}
