<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Tests\Security\Authorization\Voter;

use PHPUnit\Framework\TestCase;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use RZ\Roadiz\CoreBundle\Enum\FieldType;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeTypeFieldVoter;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

final class NodeTypeFieldVoterTest extends TestCase
{
    private function createVoter(AccessDecisionManagerInterface $accessDecisionManager): NodeTypeFieldVoter
    {
        return new NodeTypeFieldVoter($accessDecisionManager);
    }

    private function createAccessDecisionManager(bool $granted): AccessDecisionManagerInterface
    {
        $manager = $this->createMock(AccessDecisionManagerInterface::class);
        $manager->method('decide')->willReturn($granted);

        return $manager;
    }

    private function createToken(): TokenInterface
    {
        return $this->createMock(TokenInterface::class);
    }

    private function createField(FieldType $type): NodeTypeField
    {
        $field = new NodeTypeField();
        $field->setType($type);

        return $field;
    }

    public function testViewGrantedForPlainFieldRegardlessOfRoles(): void
    {
        // STRING_T is not nodes/documents/user/custom-forms: no role is even consulted.
        $voter = $this->createVoter($this->createAccessDecisionManager(false));

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($this->createToken(), $this->createField(FieldType::STRING_T), [NodeTypeFieldVoter::VIEW]),
        );
    }

    public function testViewGrantedForNodesFieldWhenSearchGranted(): void
    {
        $manager = $this->createMock(AccessDecisionManagerInterface::class);
        $manager->method('decide')->with(self::anything(), [NodeVoter::SEARCH])->willReturn(true);
        $voter = $this->createVoter($manager);

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($this->createToken(), $this->createField(FieldType::NODES_T), [NodeTypeFieldVoter::VIEW]),
        );
    }

    public function testViewDeniedForNodesFieldWhenSearchDenied(): void
    {
        $voter = $this->createVoter($this->createAccessDecisionManager(false));

        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($this->createToken(), $this->createField(FieldType::NODES_T), [NodeTypeFieldVoter::VIEW]),
        );
    }

    public function testViewGrantedForDocumentsFieldWhenRoleGranted(): void
    {
        $voter = $this->createVoter($this->createAccessDecisionManager(true));

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($this->createToken(), $this->createField(FieldType::DOCUMENTS_T), [NodeTypeFieldVoter::VIEW]),
        );
    }

    public function testViewDeniedForDocumentsFieldWhenRoleMissing(): void
    {
        $voter = $this->createVoter($this->createAccessDecisionManager(false));

        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($this->createToken(), $this->createField(FieldType::DOCUMENTS_T), [NodeTypeFieldVoter::VIEW]),
        );
    }

    public function testViewGrantedForUserFieldWhenRoleGranted(): void
    {
        $voter = $this->createVoter($this->createAccessDecisionManager(true));

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($this->createToken(), $this->createField(FieldType::USER_T), [NodeTypeFieldVoter::VIEW]),
        );
    }

    public function testViewDeniedForUserFieldWhenRoleMissing(): void
    {
        $voter = $this->createVoter($this->createAccessDecisionManager(false));

        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($this->createToken(), $this->createField(FieldType::USER_T), [NodeTypeFieldVoter::VIEW]),
        );
    }

    public function testViewGrantedForCustomFormsFieldWhenRoleGranted(): void
    {
        $voter = $this->createVoter($this->createAccessDecisionManager(true));

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($this->createToken(), $this->createField(FieldType::CUSTOM_FORMS_T), [NodeTypeFieldVoter::VIEW]),
        );
    }

    public function testViewDeniedForCustomFormsFieldWhenRoleMissing(): void
    {
        $voter = $this->createVoter($this->createAccessDecisionManager(false));

        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($this->createToken(), $this->createField(FieldType::CUSTOM_FORMS_T), [NodeTypeFieldVoter::VIEW]),
        );
    }
}
