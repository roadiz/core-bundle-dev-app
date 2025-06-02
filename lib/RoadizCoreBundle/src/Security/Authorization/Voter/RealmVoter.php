<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Security\Authorization\Voter;

use RZ\Roadiz\CoreBundle\Model\RealmInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends Voter<'read'|'password', RealmInterface>
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
    public function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        return match ($subject->getType()) {
            RealmInterface::TYPE_PLAIN_PASSWORD => $this->voteForPassword($attribute, $subject, $token),
            RealmInterface::TYPE_USER => $this->voteForUser($attribute, $subject, $token),
            RealmInterface::TYPE_ROLE => $this->voteForRole($attribute, $subject, $token),
            default => false,
        };
    }

    private function voteForRole(string $attribute, RealmInterface $subject, TokenInterface $token): bool
    {
        if (null === $role = $subject->getRole()) {
            return false;
        }

        return $this->security->isGranted($role);
    }

    private function voteForUser(string $attribute, RealmInterface $subject, TokenInterface $token): bool
    {
        if (0 === $subject->getUsers()->count() || null === $token->getUser()) {
            return false;
        }

        return $subject->getUsers()->exists(fn ($key, UserInterface $user) => $user->getUserIdentifier() === $token->getUserIdentifier());
    }

    private function voteForPassword(string $attribute, RealmInterface $subject, TokenInterface $token): bool
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request || empty($subject->getPlainPassword())) {
            return false;
        }

        return $request->query->has(self::PASSWORD_QUERY_PARAMETER)
            && $request->query->get(self::PASSWORD_QUERY_PARAMETER) === $subject->getPlainPassword();
    }
}
