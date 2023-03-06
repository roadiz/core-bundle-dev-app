<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Bag\Settings;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Form\Constraint\RecaptchaServiceInterface;
use RZ\Roadiz\CoreBundle\Mailer\EmailManager;
use RZ\Roadiz\Random\TokenGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\Mime\Address;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class PasswordRequestController
{
    use RecaptchaProtectedControllerTrait;

    private LoggerInterface $logger;
    private RateLimiterFactory $passwordRequestLimiter;
    private ManagerRegistry $managerRegistry;
    private EmailManager $emailManager;
    private Settings $settingsBag;
    private TranslatorInterface $translator;
    private UrlGeneratorInterface $urlGenerator;
    private RecaptchaServiceInterface $recaptchaService;
    private string $passwordResetUrl;
    private string $recaptchaHeaderName;

    public function __construct(
        LoggerInterface $logger,
        RateLimiterFactory $passwordRequestLimiter,
        ManagerRegistry $managerRegistry,
        EmailManager $emailManager,
        Settings $settingsBag,
        TranslatorInterface $translator,
        UrlGeneratorInterface $urlGenerator,
        RecaptchaServiceInterface $recaptchaService,
        string $passwordResetUrl,
        string $recaptchaHeaderName = 'x-g-recaptcha-response'
    ) {
        $this->logger = $logger;
        $this->passwordRequestLimiter = $passwordRequestLimiter;
        $this->managerRegistry = $managerRegistry;
        $this->emailManager = $emailManager;
        $this->passwordResetUrl = $passwordResetUrl;
        $this->settingsBag = $settingsBag;
        $this->translator = $translator;
        $this->urlGenerator = $urlGenerator;
        $this->recaptchaService = $recaptchaService;
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

    public function __invoke(Request $request, ?User $data): User
    {
        $limiter = $this->passwordRequestLimiter->create($request->getClientIp());
        $limit = $limiter->consume();
        if (false === $limit->isAccepted()) {
            throw new TooManyRequestsHttpException($limit->getRetryAfter()->getTimestamp());
        }

        $this->validateRecaptchaHeader($request);

        /*
         * Do not output anything if user exists or not to prevent search attacks.
         */
        if ($data === null) {
            return new User();
        }

        try {
            $tokenGenerator = new TokenGenerator($this->logger);
            $data->setPasswordRequestedAt(new \DateTime());
            $data->setConfirmationToken($tokenGenerator->generateToken());
            $this->sendPasswordResetLink($request, $data);
        } catch (\Exception $e) {
            $data->setPasswordRequestedAt(null);
            $data->setConfirmationToken(null);
            $this->logger->error($e->getMessage());
        }
        /*
         * This operation should not call WriteListener
         * Make sure you configured: `write: false`
         */
        $this->managerRegistry->getManager()->flush();
        return $data;
    }

    private function sendPasswordResetLink(Request $request, User $user): void
    {
        $emailContact = $this->settingsBag->get('email_sender');
        $siteName = $this->settingsBag->get('site_name');

        /*
         * Support routes name as well as hard-coded URLs
         */
        try {
            $resetLink = $this->urlGenerator->generate(
                $this->passwordResetUrl,
                [
                'token' => $user->getConfirmationToken(),
                '_locale' => $request->getLocale(),
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        } catch (RouteNotFoundException $exception) {
            $resetLink = $this->passwordResetUrl . '?' . http_build_query(
                [
                'token' => $user->getConfirmationToken(),
                '_locale' => $request->getLocale(),
                ]
            );
        }

        $this->emailManager->setAssignation(
            [
            'resetLink' => $resetLink,
            'user' => $user,
            'site' => $siteName,
            'mailContact' => $emailContact,
            ]
        );
        $this->emailManager->setEmailTemplate('@RoadizUser/email/users/reset_password_email.html.twig');
        $this->emailManager->setEmailPlainTextTemplate('@RoadizUser/email/users/reset_password_email.txt.twig');
        $this->emailManager->setSubject(
            $this->translator->trans(
                'reset.password.request'
            )
        );
        $this->emailManager->setReceiver($user->getEmail());
        $this->emailManager->setSender(new Address($emailContact, $siteName ?? ''));
        $this->emailManager->send();
    }
}
