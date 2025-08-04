<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Security\Authorization\Voter;

use RZ\Roadiz\CoreBundle\Model\RealmInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends Voter<'read', RealmInterface>
 */
final class RealmVoter extends Voter
{
    public const string READ = 'read';
    public const string PASSWORD_QUERY_PARAMETER = 'password';

    public function __construct(
        private readonly Security $security,
        private readonly RequestStack $requestStack,
    ) {
    }

    #[\Override]
    public function supportsAttribute(string $attribute): bool
    {
        return self::READ === $attribute;
    }

    #[\Override]
    protected function supports(string $attribute, mixed $subject): bool
    {
        return $this->supportsAttribute($attribute) && $subject instanceof RealmInterface;
    }

    /**
     * @param RealmInterface $subject
     */
    #[\Override]
    public function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        return match ($subject->getType()) {
            RealmInterface::TYPE_PLAIN_PASSWORD => $this->voteForPassword($attribute, $subject, $token, $vote),
            RealmInterface::TYPE_USER => $this->voteForUser($attribute, $subject, $token, $vote),
            RealmInterface::TYPE_ROLE => $this->voteForRole($attribute, $subject, $token, $vote),
            default => false,
        };
    }

    private function voteForRole(string $attribute, RealmInterface $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        if (null === $role = $subject->getRole()) {
            $vote?->addReason('Realm does not have a role defined.');

            return false;
        }

        if (!$this->security->isGranted($role)) {
            $vote?->addReason(sprintf('User does not have the role "%s".', $role));

            return false;
        }

        return true;
    }

    private function voteForUser(string $attribute, RealmInterface $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        if (0 === $subject->getUsers()->count() || null === $token->getUser()) {
            $vote?->addReason('Realm does not have any user or token user is not set.');

            return false;
        }

        return $subject->getUsers()->exists(fn ($key, UserInterface $user) => $user->getUserIdentifier() === $token->getUserIdentifier());
    }

    private function voteForPassword(string $attribute, RealmInterface $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request || empty($subject->getPlainPassword())) {
            $vote?->addReason('Realm does not have a plain password or request is not set.');

            return false;
        }

        if (!$request->query->has(self::PASSWORD_QUERY_PARAMETER)) {
            $vote?->addReason(sprintf('Realm requires a %s query parameter to be set.', self::PASSWORD_QUERY_PARAMETER));

            return false;
        }

        if ($request->query->get(self::PASSWORD_QUERY_PARAMETER) !== $subject->getPlainPassword()) {
            $vote?->addReason('Provided password does not match realm plain password.');

            return false;
        }

        return true;
    }
}
