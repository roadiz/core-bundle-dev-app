<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\Error;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\ConstraintViolationList;

interface FormErrorSerializerInterface
{
    public function getErrorsAsArray(FormInterface $form): array;

    public function getErrorsAsConstraintViolationList(FormInterface $form): ConstraintViolationList;
}
