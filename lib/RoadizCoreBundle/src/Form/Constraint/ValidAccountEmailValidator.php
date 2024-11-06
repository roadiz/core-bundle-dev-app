<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\Constraint;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\User;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ValidAccountEmailValidator extends ConstraintValidator
{
    protected ManagerRegistry $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @param ValidAccountEmail $constraint
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        $user = $this->managerRegistry
                           ->getRepository(User::class)
                           ->findOneByEmail($value);

        if (null === $user) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('%email%', $this->formatValue($value))
                ->setInvalidValue($value)
                ->addViolation();
        }
    }
}
