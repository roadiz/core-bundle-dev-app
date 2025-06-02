<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\Constraint;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @see https://github.com/thrace-project/form-bundle/blob/master/Validator/Constraint/RecaptchaValidator.php
 */
final class RecaptchaValidator extends ConstraintValidator implements RecaptchaServiceInterface
{
    public function __construct(
        protected HttpClientInterface $client,
        protected RequestStack $requestStack,
        protected ?string $recaptchaPrivateKey,
    ) {
    }

    /**
     * @see \Symfony\Component\Validator\ConstraintValidator::validate()
     */
    #[\Override]
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
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    #[\Override]
    public function check(
        string $responseValue,
        string $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify',
    ): mixed {
        if (empty($this->recaptchaPrivateKey)) {
            return true;
        }

        $response = $this->client->request('POST', $verifyUrl, [
            'query' => [
                'secret' => $this->recaptchaPrivateKey,
                'response' => $responseValue,
            ],
            'timeout' => 10,
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);
        $jsonResponse = json_decode($response->getContent(false), true);

        return (isset($jsonResponse['success']) && true === $jsonResponse['success']) ?
            (true) :
            ($jsonResponse['error-codes']);
    }
}
