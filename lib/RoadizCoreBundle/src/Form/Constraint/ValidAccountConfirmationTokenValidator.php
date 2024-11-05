<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\Constraint;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\User;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ValidAccountConfirmationTokenValidator extends ConstraintValidator
{
    protected ManagerRegistry $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @param ValidAccountConfirmationToken $constraint
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        $user = $this->managerRegistry
                           ->getRepository(User::class)
                           ->findOneByConfirmationToken($value);

        if (null === $user) {
            $this->context->addViolation($constraint->message);
        } elseif (!$user->isPasswordRequestNonExpired($constraint->ttl)) {
            $this->context->addViolation($constraint->expiredMessage);
        }
    }
}
