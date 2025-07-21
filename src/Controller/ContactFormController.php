<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\ContactFormType;
use Limenius\Liform\LiformInterface;
use RZ\Roadiz\CoreBundle\Mailer\ContactFormManagerFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;

final class ContactFormController
{
    public function __construct(
        private readonly ContactFormManagerFactory $contactFormManagerFactory,
        private readonly RateLimiterFactory $contactFormLimiter,
        private readonly LiformInterface $liform,
    ) {
    }

    public function definitionAction(Request $request): JsonResponse
    {
        $contactFormManager = $this->contactFormManagerFactory->create();
        // Do not forget to disable CSRF and form-name
        $contactFormManager
            ->setUseRealResponseCode(true)
            ->setFormName('')
            ->disableCsrfProtection();
        $builder = $contactFormManager->getFormBuilder();
        $builder->add('form', ContactFormType::class);

        $contactFormManager->withUserConsent();
        $contactFormManager->withCaptcha();

        $schema = json_encode($this->liform->transform($builder->getForm()));

        return new JsonResponse(
            $schema,
            Response::HTTP_OK,
            [],
            true
        );
    }

    public function formAction(Request $request): Response
    {
        $contactFormManager = $this->contactFormManagerFactory->create();
        // create a limiter based on a unique identifier of the client
        // (e.g. the client's IP address, a username/email, an API key, etc.)
        $limiter = $this->contactFormLimiter->create($request->getClientIp());
        // only claims 1 token if it's free at this moment (useful if you plan to skip this process)
        $limit = $limiter->consume();
        $headers = [
            'X-RateLimit-Remaining' => $limit->getRemainingTokens(),
            'X-RateLimit-Retry-After' => $limit->getRetryAfter()->getTimestamp(),
            'X-RateLimit-Limit' => $limit->getLimit(),
        ];
        // the argument of consume() is the number of tokens to consume
        // and returns an object of type Limit
        if (false === $limit->isAccepted()) {
            throw new TooManyRequestsHttpException($limit->getRetryAfter()->getTimestamp());
        }

        // Do not forget to disable CSRF and form-name
        $contactFormManager
            ->setUseRealResponseCode(true)
            ->setFormName('')
            ->disableCsrfProtection();

        $builder = $contactFormManager->getFormBuilder();
        $builder->add('form', ContactFormType::class);

        $contactFormManager->withUserConsent();
        $contactFormManager->withCaptcha();

        if (null !== $response = $contactFormManager->handle()) {
            $response->headers->add($headers);

            return $response;
        }
        throw new BadRequestHttpException('Form has not been submitted.');
    }
}
