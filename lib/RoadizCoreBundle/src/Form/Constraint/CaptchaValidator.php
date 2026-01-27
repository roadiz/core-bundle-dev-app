<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\Constraint;

use RZ\Roadiz\CoreBundle\Captcha\CaptchaServiceInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class CaptchaValidator extends ConstraintValidator
{
    public function __construct(
        protected CaptchaServiceInterface $captchaService,
        protected RequestStack $requestStack,
    ) {
    }

    /**
     * @see ConstraintValidator::validate()
     */
    #[\Override]
    public function validate(mixed $data, Constraint $constraint): void
    {
        if (!$constraint instanceof Captcha) {
            throw new \UnexpectedValueException(sprintf('Expected argument of type "%s", "%s" given.', Captcha::class, $constraint::class));
        }

        $propertyPath = $this->context->getPropertyPath();

        /*
         * If form data is empty, we try to get it from the current request.
         */
        if (empty($data)) {
            if (null === $request = $this->requestStack->getCurrentRequest()) {
                $this->context->buildViolation('Request is not defined')
                    ->atPath($propertyPath)
                    ->addViolation();

                return;
            }

            /*
             * Look for captcha field in POST or GET parameters.
             */
            $data = $request->request->get($this->captchaService->getFieldName())
                ?? $request->query->get($this->captchaService->getFieldName());
        }

        if (empty($data)) {
            $this->context->buildViolation($constraint->emptyMessage)
                ->atPath($propertyPath)
                ->addViolation();
        } elseif (true !== $response = $this->captchaService->check($data)) {
            $cause = null;
            if (is_array($response)) {
                $cause = implode(', ', $response);
            } elseif (is_string($response)) {
                $cause = $response;
            }

            $this->context->buildViolation($constraint->invalidMessage)
                ->setCause($cause)
                ->atPath($propertyPath)
                ->addViolation();
        }
    }
}
