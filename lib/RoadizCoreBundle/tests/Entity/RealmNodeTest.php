<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use RZ\Roadiz\CoreBundle\Entity\Realm;
use RZ\Roadiz\CoreBundle\Entity\RealmNode;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RealmNodeTest extends TestCase
{
    private function getValidator(): ValidatorInterface
    {
        return Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public function testRealmIsRequired(): void
    {
        // Regression for #450: a RealmNode submitted without a realm used to leave
        // the non-nullable typed property uninitialized, throwing
        // "must not be accessed before initialization" when getRealm() was later called.
        $realmNode = new RealmNode();

        $violations = $this->getValidator()->validateProperty($realmNode, 'realm');

        $this->assertCount(1, $violations);
        $this->assertSame('realm', $violations->get(0)->getPropertyPath());
    }

    public function testRealmPassesValidationWhenSet(): void
    {
        $realmNode = new RealmNode();
        $realmNode->setRealm(new Realm());

        $violations = $this->getValidator()->validateProperty($realmNode, 'realm');

        $this->assertCount(0, $violations);
    }
}
