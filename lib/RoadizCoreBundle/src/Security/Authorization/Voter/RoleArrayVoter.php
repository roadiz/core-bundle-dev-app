<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Security\Authorization\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\RoleVoter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * @deprecated BC break temporary fix for is_granted on Role array. Twig templates and controller should not test Role arrays.
 */
class RoleArrayVoter extends RoleVoter
{
    /**
     * {@inheritdoc}
     */
    public function vote(TokenInterface $token, $subject, array $attributes): int
    {
        if (isset($attributes[0]) && !\is_array($attributes[0])) {
            return parent::vote($token, $subject, $attributes);
        }

        $result = VoterInterface::ACCESS_ABSTAIN;
        $roles = $this->extractRoles($token);

        foreach ($attributes as $attribute) {
            if (!\is_array($attribute)) {
                continue;
            }

            foreach ($attribute as $singleAttribute) {
                if (!\is_string($singleAttribute)) {
                    continue;
                }

                if ('ROLE_PREVIOUS_ADMIN' === $singleAttribute) {
                    trigger_deprecation('symfony/security-core', '5.1', 'The ROLE_PREVIOUS_ADMIN role is deprecated and will be removed in version 6.0, use the IS_IMPERSONATOR attribute instead.');
                }

                $result = VoterInterface::ACCESS_DENIED;
                if (\in_array($singleAttribute, $roles, true)) {
                    return VoterInterface::ACCESS_GRANTED;
                }
            }
        }

        return $result;
    }
}
