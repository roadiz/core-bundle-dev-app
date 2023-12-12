<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Security\Authorization\Voter;

use RZ\Roadiz\CoreBundle\Entity\Realm;
use RZ\Roadiz\CoreBundle\Model\RealmInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

final class RealmVoter extends Voter
{
    public const READ = 'read';
    public const PASSWORD_QUERY_PARAMETER = 'password';

    private Security $security;
    private RequestStack $requestStack;

    public function __construct(Security $security, RequestStack $requestStack)
    {
        $this->security = $security;
        $this->requestStack = $requestStack;
    }

    public function supportsAttribute(string $attribute): bool
    {
        return $attribute === self::READ;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return $this->supportsAttribute($attribute) && $subject instanceof RealmInterface;
    }

    /**
     * @param string $attribute
     * @param Realm $subject
     * @param TokenInterface $token
     * @return bool
     */
    public function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        return match ($subject->getType()) {
            RealmInterface::TYPE_PLAIN_PASSWORD => $this->voteForPassword($attribute, $subject, $token),
            RealmInterface::TYPE_USER => $this->voteForUser($attribute, $subject, $token),
            RealmInterface::TYPE_ROLE => $this->voteForRole($attribute, $subject, $token),
            default => false,
        };
    }

    /**
     * @param string $attribute
     * @param RealmInterface $subject
     * @param TokenInterface $token
     * @return bool
     */
    private function voteForRole(string $attribute, RealmInterface $subject, TokenInterface $token): bool
    {
        if (null === $role = $subject->getRole()) {
            return false;
        }
        return $this->security->isGranted($role);
    }

    /**
     * @param string $attribute
     * @param RealmInterface $subject
     * @param TokenInterface $token
     * @return bool
     */
    private function voteForUser(string $attribute, RealmInterface $subject, TokenInterface $token): bool
    {
        if ($subject->getUsers()->count() === 0 || null === $token->getUser()) {
            return false;
        }
        return $subject->getUsers()->exists(function ($key, UserInterface $user) use ($token) {
            return $user->getUserIdentifier() === $token->getUserIdentifier();
        });
    }

    /**
     * @param string $attribute
     * @param RealmInterface $subject
     * @param TokenInterface $token
     * @return bool
     */
    private function voteForPassword(string $attribute, RealmInterface $subject, TokenInterface $token): bool
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request || empty($subject->getPlainPassword())) {
            return false;
        }
        return $request->query->has(self::PASSWORD_QUERY_PARAMETER) &&
            $request->query->get(self::PASSWORD_QUERY_PARAMETER) === $subject->getPlainPassword();
    }
}
