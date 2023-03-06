<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\Controller;

use ApiPlatform\Validator\ValidatorInterface;
use RZ\Roadiz\CoreBundle\Bag\Settings;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Form\Constraint\RecaptchaServiceInterface;
use RZ\Roadiz\UserBundle\Event\UserSignedUp;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class SignupController
{
    use RecaptchaProtectedControllerTrait;

    private Security $security;
    private ValidatorInterface $validator;
    private EventDispatcherInterface $eventDispatcher;
    private RateLimiterFactory $userSignupLimiter;
    private RecaptchaServiceInterface $recaptchaService;
    private Settings $settingsBag;
    private string $recaptchaHeaderName;

    public function __construct(
        ValidatorInterface $validator,
        Security $security,
        EventDispatcherInterface $eventDispatcher,
        RateLimiterFactory $userSignupLimiter,
        RecaptchaServiceInterface $recaptchaService,
        Settings $settingsBag,
        string $recaptchaHeaderName = 'x-g-recaptcha-response'
    ) {
        $this->validator = $validator;
        $this->security = $security;
        $this->eventDispatcher = $eventDispatcher;
        $this->userSignupLimiter = $userSignupLimiter;
        $this->recaptchaService = $recaptchaService;
        $this->settingsBag = $settingsBag;
        $this->recaptchaHeaderName = $recaptchaHeaderName;
    }

    protected function getRecaptchaService(): RecaptchaServiceInterface
    {
        return $this->recaptchaService;
    }

    protected function getSettingsBag(): Settings
    {
        return $this->settingsBag;
    }

    protected function getRecaptchaHeaderName(): string
    {
        return $this->recaptchaHeaderName;
    }

    public function __invoke(Request $request, User $data): User
    {
        if ($this->security->isGranted('ROLE_USER')) {
            throw new AccessDeniedHttpException('Cannot sign-up: you\'re already authenticated.');
        }
        $limiter = $this->userSignupLimiter->create($request->getClientIp());
        $limit = $limiter->consume();
        if (false === $limit->isAccepted()) {
            throw new TooManyRequestsHttpException($limit->getRetryAfter()->getTimestamp());
        }

        $this->validateRecaptchaHeader($request);

        $this->validator->validate($data);
        $data->setLocale($request->getLocale());
        $this->eventDispatcher->dispatch(new UserSignedUp($data));

        return $data;
    }
}
