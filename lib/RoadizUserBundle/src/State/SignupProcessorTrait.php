<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\State;

use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\UserBundle\Api\Dto\AbstractUserInput;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;

trait SignupProcessorTrait
{
    abstract protected function getSecurity(): Security;

    abstract protected function getUserSignupLimiter(): RateLimiterFactory;

    protected function validateRequest(?Request $request): void
    {
        if ($this->getSecurity()->isGranted('ROLE_USER')) {
            throw new AccessDeniedHttpException('Cannot sign-up: you\'re already authenticated.');
        }

        if (null !== $request) {
            $limiter = $this->getUserSignupLimiter()->create($request->getClientIp());
            $limit = $limiter->consume();
            if (false === $limit->isAccepted()) {
                throw new TooManyRequestsHttpException($limit->getRetryAfter()->getTimestamp());
            }
        }
    }

    protected function createUser(AbstractUserInput $data): User
    {
        $user = new User();
        $user->setEmail($data->email);
        $user->setUsername($data->email);
        $user->setFirstName($data->firstName);
        $user->setLastName($data->lastName);
        $user->setPublicName($data->publicName);
        $user->setCompany($data->company);

        return $user;
    }
}
