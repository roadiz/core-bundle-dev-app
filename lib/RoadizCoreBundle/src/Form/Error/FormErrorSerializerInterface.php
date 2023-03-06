<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\Error;

use Symfony\Component\Form\FormInterface;

interface FormErrorSerializerInterface
{
    public function getErrorsAsArray(FormInterface $form): array;
}
