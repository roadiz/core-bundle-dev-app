<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\ContactFormType;
use Limenius\Liform\LiformInterface;
use RZ\Roadiz\CoreBundle\Form\Constraint\Recaptcha;
use RZ\Roadiz\CoreBundle\Mailer\ContactFormManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;

final class ContactFormController
{
    private ContactFormManager $contactFormManager;
    private RateLimiterFactory $contactFormLimiter;
    private LiformInterface $liform;

    public function __construct(
        ContactFormManager $contactFormManager,
        RateLimiterFactory $contactFormLimiter,
        LiformInterface $liform
    ) {
        $this->contactFormManager = $contactFormManager;
        $this->contactFormLimiter = $contactFormLimiter;
        $this->liform = $liform;
    }

    public function definitionAction(Request $request): JsonResponse
    {
        // Do not forget to disable CSRF and form-name
        $this->contactFormManager
            ->setUseRealResponseCode(true)
            ->setFormName('')
            ->disableCsrfProtection();
        $builder = $this->contactFormManager->getFormBuilder();
        $builder->add('form', ContactFormType::class);

        $this->contactFormManager->withUserConsent();
        //$this->contactFormManager->withGoogleRecaptcha(Recaptcha::FORM_NAME);

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
        $this->contactFormManager
            ->setUseRealResponseCode(true)
            ->setFormName('')
            ->disableCsrfProtection();

        $builder = $this->contactFormManager->getFormBuilder();
        $builder->add('form', ContactFormType::class);

        $this->contactFormManager->withUserConsent();
        //$this->contactFormManager->withGoogleRecaptcha(Recaptcha::FORM_NAME);

        if (null !== $response = $this->contactFormManager->handle()) {
            $response->headers->add($headers);
            return $response;
        }
        throw new BadRequestHttpException('Form has not been submitted.');
    }
}
