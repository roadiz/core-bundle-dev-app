<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\Error;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class FormErrorSerializer implements FormErrorSerializerInterface
{
    public function __construct(private TranslatorInterface $translator)
    {
    }

    #[\Override]
    public function getErrorsAsArray(FormInterface $form): array
    {
        $errors = [];
        /** @var FormError $error */
        foreach ($form->getErrors() as $error) {
            if (null !== $error->getOrigin()) {
                $errorFieldName = $error->getOrigin()->getName();
                if (count($error->getMessageParameters()) > 0) {
                    $errors[$errorFieldName] = $this->translator->trans(
                        $error->getMessageTemplate(),
                        $error->getMessageParameters(),
                        domain: 'validators',
                    );
                } else {
                    $errors[$errorFieldName] = $this->translator->trans(
                        $error->getMessage(),
                        domain: 'validators',
                    );
                }
                $cause = $error->getCause();
                if (null !== $cause) {
                    if ($cause instanceof ConstraintViolation) {
                        $cause = $cause->getCause();
                    }
                    if ($cause instanceof \Exception) {
                        $errors[$errorFieldName.'_cause_message'] = $cause->getMessage();
                    }
                    if (is_object($cause)) {
                        $errors[$errorFieldName.'_cause'] = $cause::class;
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

    protected function getConstraintViolations(FormInterface $form): \Generator
    {
        /** @var FormError $error */
        foreach ($form->getErrors(true, true) as $error) {
            $cause = $error->getCause();
            if ($cause instanceof ConstraintViolation) {
                yield new ConstraintViolation(
                    $cause->getMessage(),
                    $cause->getMessageTemplate(),
                    $cause->getParameters(),
                    $cause->getRoot(),
                    (null !== $error->getOrigin()) ? $this->getPropertyPath($error->getOrigin()) : null,
                    $cause->getInvalidValue(),
                    $cause->getPlural(),
                    $cause->getCode(),
                    $cause->getConstraint(),
                    $cause->getCause(),
                );
            } else {
                if (count($error->getMessageParameters()) > 0) {
                    $translatedMessage = $this->translator->trans($error->getMessageTemplate(), $error->getMessageParameters());
                } else {
                    $translatedMessage = $this->translator->trans($error->getMessage());
                }
                /*
                 * Make the reverse transformation that ViolationMapperInterface does.
                 * @see \Symfony\Component\Form\Extension\Validator\ViolationMapper\ViolationMapperInterface
                 */
                yield new ConstraintViolation(
                    $translatedMessage,
                    $error->getMessageTemplate(),
                    $error->getMessageParameters(),
                    root: $form,
                    propertyPath: (null !== $error->getOrigin()) ? $this->getPropertyPath($error->getOrigin()) : '',
                    invalidValue: (null !== $error->getOrigin()) ? $error->getOrigin()->getData() : null,
                );
            }
        }
    }

    protected function getPropertyPath(FormInterface $form): string
    {
        $parts = [
            $form->getName(),
        ];
        while (null !== $parent = $form->getParent()) {
            array_unshift($parts, $parent->getName());
            $form = $parent;
        }

        return implode('.', array_filter($parts));
    }

    #[\Override]
    public function getErrorsAsConstraintViolationList(FormInterface $form): ConstraintViolationList
    {
        return new ConstraintViolationList($this->getConstraintViolations($form));
    }
}
