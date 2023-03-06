<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\Constraint;

use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use Symfony\Component\Validator\Constraint;

/**
 * Constraint for the Unique Entity validator.
 *
 * @Annotation
 * @Target({"CLASS"})
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 * @see https://github.com/symfony/doctrine-bridge/blob/master/Validator/Constraints/UniqueEntity.php
 * @deprecated Use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity
 */
class UniqueEntity extends Constraint
{
    public const NOT_UNIQUE_ERROR = '23bd9dbf-6b9b-41cd-a99e-4844bcf3077f';

    public string $message = 'value.is.already.used';
    /**
     * @var class-string<PersistableInterface>|null
     */
    public ?string $entityClass = null;
    public string $repositoryMethod = 'findBy';
    public ?string $errorPath = null;
    public array $fields = [];
    public bool $ignoreNull = true;

    public function getRequiredOptions(): array
    {
        return ['fields'];
    }

    public function getDefaultOption(): string
    {
        return 'fields';
    }
}
