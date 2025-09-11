<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Security\Authorization\Voter;

use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class NodeTypeFieldVoter extends Voter
{
    public const string VIEW = 'VIEW';

    public function __construct(
        private readonly AccessDecisionManagerInterface $accessDecisionManager,
    ) {
    }

    #[\Override]
    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!\in_array($attribute, [self::VIEW], true)) {
            return false;
        }

        return $subject instanceof NodeTypeField;
    }

    #[\Override]
    public function supportsAttribute(string $attribute): bool
    {
        return in_array($attribute, [self::VIEW], true);
    }

    #[\Override]
    public function supportsType(string $subjectType): bool
    {
        return is_a($subjectType, NodeTypeField::class, true);
    }

    #[\Override]
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        return match ($attribute) {
            self::VIEW => $this->canView($subject, $token, $vote),
            default => throw new \LogicException('This code should not be reached!'),
        };
    }

    private function canView(NodeTypeField $field, TokenInterface $token, ?Vote $vote = null): bool
    {
        if ($field->isNodes() && !$this->accessDecisionManager->decide($token, [NodeVoter::SEARCH])) {
            $vote?->addReason('You must be granted with NodeVoter::SEARCH permission to view nodes fields.');

            return false;
        }
        if ($field->isDocuments() && !$this->accessDecisionManager->decide($token, ['ROLE_ACCESS_DOCUMENTS'])) {
            $vote?->addReason('You must be granted with ROLE_ACCESS_DOCUMENTS to view documents fields.');

            return false;
        }
        if ($field->isUser() && !$this->accessDecisionManager->decide($token, ['ROLE_ACCESS_USERS'])) {
            $vote?->addReason('You must be granted with ROLE_ACCESS_USERS to view users fields.');

            return false;
        }
        if ($field->isCustomForms() && !$this->accessDecisionManager->decide($token, ['ROLE_ACCESS_CUSTOMFORMS'])) {
            $vote?->addReason('You must be granted with ROLE_ACCESS_CUSTOMFORMS to view custom-form fields.');

            return false;
        }

        return true;
    }
}
