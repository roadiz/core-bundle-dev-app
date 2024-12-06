<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\Error;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Contracts\Translation\TranslatorInterface;

final class FormErrorSerializer implements FormErrorSerializerInterface
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function getErrorsAsArray(FormInterface $form): array
    {
        $errors = [];
        /** @var FormError $error */
        foreach ($form->getErrors() as $error) {
            if (null !== $error->getOrigin()) {
                $errorFieldName = $error->getOrigin()->getName();
                if (count($error->getMessageParameters()) > 0) {
                    $errors[$errorFieldName] = $this->translator->trans($error->getMessageTemplate(), $error->getMessageParameters());
                } else {
                    $errors[$errorFieldName] = $this->translator->trans($error->getMessage());
                }
                $cause = $error->getCause();
                if (null !== $cause) {
                    if ($cause instanceof ConstraintViolation) {
                        $cause = $cause->getCause();
                    }
                    if (is_object($cause)) {
                        if ($cause instanceof \Exception) {
                            $errors[$errorFieldName.'_cause_message'] = $cause->getMessage();
                        }
                        $errors[$errorFieldName.'_cause'] = get_class($cause);
                    }
                }
            }
        }

        foreach ($form->all() as $key => $child) {
            $err = $this->getErrorsAsArray($child);
            if ($err) {
                $errors[$key] = $err;
            }
        }

        return $errors;
    }
}
