<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Security\Authentication;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PropertyAccess\Exception\AccessException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

abstract class RoadizAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'loginPage';

    private UrlGeneratorInterface $urlGenerator;
    private ManagerRegistry $managerRegistry;
    private LoggerInterface $logger;
    private string $usernamePath;
    private string $passwordPath;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        ManagerRegistry $managerRegistry,
        LoggerInterface $logger,
        string $usernamePath = 'username',
        string $passwordPath = 'password'
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->managerRegistry = $managerRegistry;
        $this->logger = $logger;
        $this->usernamePath = $usernamePath;
        $this->passwordPath = $passwordPath;
    }

    public function authenticate(Request $request): Passport
    {
        $credentials = $this->getCredentials($request);
        $request->getSession()->set(Security::LAST_USERNAME, $credentials['username']);

        return new Passport(
            new UserBadge($credentials['username']),
            new PasswordCredentials($credentials['password']),
            [
                new CsrfTokenBadge('authenticate', $request->get('_csrf_token')),
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): Response
    {
        $user = $token->getUser();

        if ($user instanceof User) {
            $user->setLastLogin(new \DateTime('now'));
            $manager = $this->managerRegistry->getManagerForClass(User::class);
            if (null !== $manager) {
                $manager->flush();
            }
        }

        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('adminHomePage'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $credentials = $this->getCredentials($request);
        $ipAddress = $request->getClientIp();
        $this->logger->error($exception->getMessage(), [
            'username' => $credentials['username'],
            'ipAddress' => $ipAddress
        ]);

        return parent::onAuthenticationFailure($request, $exception);
    }


    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }

    /**
     * @param Request $request
     * @return array<string>
     */
    private function getCredentials(Request $request): array
    {
        $credentials = [];
        try {
            $credentials['username'] = $request->request->get($this->usernamePath);

            if (!\is_string($credentials['username'])) {
                throw new BadRequestHttpException(sprintf('The key "%s" must be a string.', $this->usernamePath));
            }

            if (\mb_strlen($credentials['username']) > Security::MAX_USERNAME_LENGTH) {
                throw new BadCredentialsException('Invalid username.');
            }
        } catch (AccessException $e) {
            throw new BadRequestHttpException(sprintf('The key "%s" must be provided.', $this->usernamePath), $e);
        }

        try {
            $credentials['password'] = $request->request->get($this->passwordPath);

            if (!\is_string($credentials['password'])) {
                throw new BadRequestHttpException(sprintf('The key "%s" must be a string.', $this->passwordPath));
            }
        } catch (AccessException $e) {
            throw new BadRequestHttpException(sprintf('The key "%s" must be provided.', $this->passwordPath), $e);
        }

        return $credentials;
    }
}
