<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Security\User;

use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Bag\Settings;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Mailer\EmailManager;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserViewer
{
    protected Settings $settingsBag;
    protected UrlGeneratorInterface $urlGenerator;
    protected TranslatorInterface $translator;
    protected EmailManager $emailManager;
    protected LoggerInterface $logger;
    protected ?User $user = null;

    public function __construct(
        Settings $settingsBag,
        UrlGeneratorInterface $urlGenerator,
        TranslatorInterface $translator,
        EmailManager $emailManager,
        LoggerInterface $logger
    ) {
        $this->settingsBag = $settingsBag;
        $this->translator = $translator;
        $this->emailManager = $emailManager;
        $this->logger = $logger;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Send email to reset user password.
     *
     * @param string|NodesSources $route
     * @param string $htmlTemplate
     * @param string $txtTemplate
     *
     * @return bool
     * @throws \Exception
     */
    public function sendPasswordResetLink(
        $route = 'loginResetPage',
        string $htmlTemplate = '@RoadizCore/email/users/reset_password_email.html.twig',
        string $txtTemplate = '@RoadizCore/email/users/reset_password_email.txt.twig'
    ): bool {
        if (null === $this->user) {
            throw new \InvalidArgumentException('User should be defined before sending email.');
        }
        $emailContact = $this->getContactEmail();
        $siteName = $this->getSiteName();

        if (is_string($route)) {
            $resetLink = $this->urlGenerator->generate(
                $route,
                [
                    'token' => $this->user->getConfirmationToken(),
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        } else {
            $resetLink = $this->urlGenerator->generate(
                RouteObjectInterface::OBJECT_BASED_ROUTE_NAME,
                [
                    RouteObjectInterface::ROUTE_OBJECT => $route,
                    'token' => $this->user->getConfirmationToken(),
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        }
        $this->emailManager->setAssignation([
            'resetLink' => $resetLink,
            'user' => $this->user,
            'site' => $siteName,
            'mailContact' => $emailContact,
        ]);
        $this->emailManager->setEmailTemplate($htmlTemplate);
        $this->emailManager->setEmailPlainTextTemplate($txtTemplate);
        $this->emailManager->setSubject($this->translator->trans(
            'reset.password.request'
        ));
        $this->emailManager->setReceiver($this->user->getEmail());
        $this->emailManager->setSender([$emailContact => $siteName]);

        try {
            // Send the message
            $this->emailManager->send();
            return true;
        } catch (TransportException $e) {
            // Silent error not to prevent user creation if mailer is not configured
            $this->logger->error('Unable to send password reset link', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * @return string
     */
    protected function getContactEmail(): string
    {
        $emailContact = $this->settingsBag->get('email_sender') ?? '';
        if (empty($emailContact)) {
            $emailContact = "noreply@roadiz.io";
        }

        return $emailContact;
    }

    /**
     * @return string
     */
    protected function getSiteName(): string
    {
        $siteName = $this->settingsBag->get('site_name') ?? '';
        if (empty($siteName)) {
            $siteName = "Unnamed site";
        }

        return $siteName;
    }

    /**
     * @return null|User
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param null|User $user
     * @return UserViewer
     */
    public function setUser(?User $user)
    {
        $this->user = $user;
        return $this;
    }
}
