<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Security\Authorization\Voter;

use RZ\Roadiz\CoreBundle\Model\RealmInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
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
    public const string AUTHORIZATION_SCHEME = 'PasswordQuery';

    public function __construct(
        private readonly AccessDecisionManagerInterface $accessDecisionManager,
        private readonly RequestStack $requestStack,
    ) {
    }

    #[\Override]
    public function supportsAttribute(string $attribute): bool
    {
        return self::READ === $attribute;
    }

    #[\Override]
    public function supportsType(string $subjectType): bool
    {
        return is_a($subjectType, RealmInterface::class, true);
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

        if (!$this->accessDecisionManager->decide($token, [$role])) {
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
        $storedPassword = $subject->getPlainPassword();

        if (null === $request || empty($storedPassword)) {
            $vote?->addReason('Realm does not have a plain password or request is not set.');

            return false;
        }

        $submittedPassword = $this->extractPassword($request);
        if (null === $submittedPassword) {
            $vote?->addReason(sprintf(
                'Realm requires a %s query parameter or an Authorization header with scheme %s.',
                self::PASSWORD_QUERY_PARAMETER,
                self::AUTHORIZATION_SCHEME,
            ));

            return false;
        }

        if (!$this->verifyPassword($submittedPassword, $storedPassword)) {
            $vote?->addReason('Provided password does not match realm password.');

            return false;
        }

        return true;
    }

    /**
     * Extract the submitted password from the Authorization header (preferred) or query parameter (legacy).
     */
    private function extractPassword(Request $request): ?string
    {
        $authorization = $request->headers->get('Authorization', '');
        if (null !== $authorization && str_starts_with($authorization, self::AUTHORIZATION_SCHEME.' ')) {
            return substr($authorization, \strlen(self::AUTHORIZATION_SCHEME) + 1);
        }

        if ($request->query->has(self::PASSWORD_QUERY_PARAMETER)) {
            return $request->query->get(self::PASSWORD_QUERY_PARAMETER);
        }

        return null;
    }

    /**
     * Verify a submitted password against the stored password.
     * Supports bcrypt hashes (new) and falls back to timing-safe comparison for legacy plaintext passwords.
     */
    private function verifyPassword(string $submittedPassword, string $storedPassword): bool
    {
        // New bcrypt-hashed passwords
        if (\password_verify($submittedPassword, $storedPassword)) {
            return true;
        }

        // Legacy plaintext passwords: use timing-safe comparison
        return \hash_equals($storedPassword, $submittedPassword);
    }
}
