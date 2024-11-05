<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\Constraint;

use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @see https://github.com/thrace-project/form-bundle/blob/master/Validator/Constraint/RecaptchaValidator.php
 */
class RecaptchaValidator extends ConstraintValidator implements RecaptchaServiceInterface
{
    protected RequestStack $requestStack;
    protected ?string $recaptchaPrivateKey;

    public function __construct(RequestStack $requestStack, ?string $recaptchaPrivateKey)
    {
        $this->requestStack = $requestStack;
        $this->recaptchaPrivateKey = $recaptchaPrivateKey;
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @see \Symfony\Component\Validator\ConstraintValidator::validate()
     */
    public function validate(mixed $data, Constraint $constraint): void
    {
        if ($constraint instanceof Recaptcha) {
            $propertyPath = $this->context->getPropertyPath();

            if (null === $this->requestStack->getCurrentRequest()) {
                $this->context->buildViolation('Request is not defined')
                    ->atPath($propertyPath)
                    ->addViolation();
            }

            $responseField = $this->requestStack->getCurrentRequest()->get($constraint->fieldName);

            if (empty($responseField)) {
                $this->context->buildViolation($constraint->emptyMessage)
                    ->atPath($propertyPath)
                    ->addViolation();
            } elseif (true !== $response = $this->check($responseField, $constraint->verifyUrl)) {
                $this->context->buildViolation($constraint->invalidMessage)
                    ->atPath($propertyPath)
                    ->addViolation();

                if (is_array($response)) {
                    foreach ($response as $errorCode) {
                        $this->context->buildViolation($errorCode)
                            ->atPath($propertyPath)
                            ->addViolation();
                    }
                } elseif (is_string($response)) {
                    $this->context->buildViolation($response)
                        ->atPath($propertyPath)
                        ->addViolation();
                }
            }
        }
    }

    /**
     * Makes a request to recaptcha service and checks if recaptcha field is valid.
     * Returns Google error-codes if recaptcha fails.
     *
     * @return true|mixed
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function check(
        string $responseValue,
        string $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify',
    ): mixed {
        if (empty($this->recaptchaPrivateKey)) {
            return true;
        }

        $data = [
            'secret' => $this->recaptchaPrivateKey,
            'response' => $responseValue,
        ];

        $client = new Client();
        $response = $client->post($verifyUrl, [
            'form_params' => $data,
            'connect_timeout' => 10,
            'timeout' => 10,
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);
        $jsonResponse = json_decode($response->getBody()->getContents(), true);

        return (isset($jsonResponse['success']) && true === $jsonResponse['success']) ?
            (true) :
            ($jsonResponse['error-codes']);
    }
}
