<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\Constraint;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\Utils\StringHandler;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueTagNameValidator extends ConstraintValidator
{
    protected ManagerRegistry $managerRegistry;

    /**
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @param string $value
     * @param UniqueTagName $constraint
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if ($this->isMulti($value)) {
            $names = explode(',', $value);
            foreach ($names as $name) {
                $name = strip_tags(trim($name));
                $this->testSingleValue($name, $constraint);
            }
        } else {
            $this->testSingleValue($value, $constraint);
        }
    }

    /**
     * @param string|null $value
     * @param UniqueTagName $constraint
     */
    protected function testSingleValue(?string $value, Constraint $constraint): void
    {
        $value = StringHandler::slugify($value ?? '');

        /*
         * If value is already the node name
         * do nothing.
         */
        if (null !== $constraint->currentValue && $value == $constraint->currentValue) {
            return;
        }

        if (true === $this->tagNameExists($value)) {
            $this->context->addViolation($constraint->message, [
                '%name%' => $value,
            ]);
        }
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    protected function tagNameExists(string $name): bool
    {
        $entity = $this->managerRegistry->getRepository(Tag::class)->findOneByTagName($name);

        return (null !== $entity);
    }

    /**
     * @param string|null $value
     * @return bool
     */
    protected function isMulti(?string $value): bool
    {
        return (bool) strpos($value ?? '', ',');
    }
}
