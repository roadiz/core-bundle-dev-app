<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\Constraint;

use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class UniqueFilenameValidator extends ConstraintValidator
{
    public function __construct(private readonly FilesystemOperator $documentsStorage)
    {
    }

    /**
     * @throws FilesystemException
     */
    #[\Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if ($constraint instanceof UniqueFilename) {
            $document = $constraint->document;

            if (null === $document) {
                return;
            }
            /*
             * If value is already the filename
             * do nothing.
             */
            if ($value == $document->getFilename()) {
                return;
            }

            $folder = $document->getMountFolderPath();

            if ($this->documentsStorage->fileExists($folder.'/'.$value)) {
                $this->context->addViolation($constraint->message);
            }
        }
    }
}
