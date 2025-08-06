<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Security\Authorization\Voter;

use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class NodeTypeFieldVoter extends Voter
{
    public const string VIEW = 'VIEW';

    public function __construct(
        private readonly Security $security,
    ) {
    }

    #[\Override]
    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!\in_array($attribute, [self::VIEW])) {
            return false;
        }

        return $subject instanceof NodeTypeField;
    }

    #[\Override]
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            // the user must be logged in; if not, deny access
            return false;
        }

        return match ($attribute) {
            self::VIEW => $this->canView($subject, $user, $vote),
            default => throw new \LogicException('This code should not be reached!'),
        };
    }

    private function canView(NodeTypeField $field, UserInterface $user, ?Vote $vote = null): bool
    {
        if ($field->isNodes() && !$this->security->isGranted(NodeVoter::SEARCH)) {
            $vote?->addReason('You must be granted with NodeVoter::SEARCH permission to view nodes fields.');

            return false;
        }
        if ($field->isDocuments() && !$this->security->isGranted('ROLE_ACCESS_DOCUMENTS')) {
            $vote?->addReason('You must be granted with ROLE_ACCESS_DOCUMENTS to view documents fields.');

            return false;
        }
        if ($field->isUser() && !$this->security->isGranted('ROLE_ACCESS_USERS')) {
            $vote?->addReason('You must be granted with ROLE_ACCESS_USERS to view users fields.');

            return false;
        }
        if ($field->isCustomForms() && !$this->security->isGranted('ROLE_ACCESS_CUSTOMFORMS')) {
            $vote?->addReason('You must be granted with ROLE_ACCESS_CUSTOMFORMS to view custom-form fields.');

            return false;
        }

        return true;
    }
}
