<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\Constraint;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\UrlAlias;
use RZ\Roadiz\CoreBundle\Repository\NodeRepository;
use RZ\Roadiz\Utils\StringHandler;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueNodeNameValidator extends ConstraintValidator
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
     * @param mixed $value
     * @param UniqueNodeName $constraint
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        $value = StringHandler::slugify($value);

        /*
         * If value is already the node name
         * do nothing.
         */
        if (null !== $constraint->currentValue && $value == $constraint->currentValue) {
            return;
        }

        if (true === $this->urlAliasExists($value)) {
            $this->context->addViolation($constraint->messageUrlAlias);
        } elseif (true === $this->nodeNameExists($value)) {
            $this->context->addViolation($constraint->message);
        }
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    protected function urlAliasExists(string $name): bool
    {
        return (bool) $this->managerRegistry->getRepository(UrlAlias::class)->exists($name);
    }

    /**
     * @param string $name
     *
     * @return bool
     * @throws \Doctrine\ORM\NonUniqueResultException|\Doctrine\ORM\NoResultException
     */
    protected function nodeNameExists(string $name): bool
    {
        /** @var NodeRepository $nodeRepo */
        $nodeRepo = $this->managerRegistry->getRepository(Node::class);
        $nodeRepo->setDisplayingNotPublishedNodes(true);
        return $nodeRepo->exists($name);
    }
}
