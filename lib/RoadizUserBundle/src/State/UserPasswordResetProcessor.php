<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\ValidatorInterface;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\UserBundle\Api\Dto\UserPasswordTokenInput;
use RZ\Roadiz\UserBundle\Api\Dto\VoidOutput;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;

/*
 * Process a user password reset token into a new password.
 */
final readonly class UserPasswordResetProcessor implements ProcessorInterface
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
        private ValidatorInterface $validator,
        private RateLimiterFactory $passwordResetLimiter,
        private RequestStack $requestStack,
        private int $passwordResetExpiresIn,
    ) {
    }

    #[\Override]
    public function process($data, Operation $operation, array $uriVariables = [], array $context = []): VoidOutput
    {
        if (!$data instanceof UserPasswordTokenInput) {
            throw new \RuntimeException(sprintf('Cannot process %s', $data::class));
        }

        $user = $this->managerRegistry
            ->getRepository(User::class)
            ->findOneByConfirmationToken($data->token);

        $request = $this->requestStack->getCurrentRequest();
        if (null !== $request) {
            $limiter = $this->passwordResetLimiter->create($request->getClientIp());
            $limit = $limiter->consume();
            if (false === $limit->isAccepted()) {
                throw new TooManyRequestsHttpException($limit->getRetryAfter()->getTimestamp());
            }
        }

        if (!$user instanceof User) {
            throw new NotFoundHttpException('User does not exist.');
        }

        if (
            !$user->isEnabled()
            || !$user->isAccountNonExpired()
            || !$user->isAccountNonLocked()
        ) {
            throw new UnprocessableEntityHttpException('User is disabled, locked or expired.');
        }

        $expiresAt = clone $user->getPasswordRequestedAt();
        $expiresAt->add(new \DateInterval(sprintf('PT%dS', $this->passwordResetExpiresIn)));

        if ($expiresAt <= new \DateTime()) {
            throw new UnprocessableEntityHttpException('Token is not valid anymore.');
        }

        $user->setPlainPassword($data->plainPassword);
        $this->validator->validate($user);

        $user->setPasswordRequestedAt(null);
        $user->setConfirmationToken(null);

        /*
         * This operation should not call WriteListener
         * Make sure you configured: `write: false`
         */
        $this->managerRegistry->getManager()->flush();

        return new VoidOutput();
    }
}
