<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\Api\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use ApiPlatform\Core\Validator\ValidatorInterface;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\UserBundle\Api\Dto\UserPasswordTokenInput;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Core\User\UserInterface;

final class UserPasswordTokenInputDataTransformer implements DataTransformerInterface
{
    private ManagerRegistry $managerRegistry;
    private ValidatorInterface $validator;
    private RateLimiterFactory $passwordResetLimiter;
    private RequestStack $requestStack;
    private int $passwordResetExpiresIn;

    public function __construct(
        ManagerRegistry $managerRegistry,
        ValidatorInterface $validator,
        RateLimiterFactory $passwordResetLimiter,
        RequestStack $requestStack,
        int $passwordResetExpiresIn
    ) {
        $this->passwordResetExpiresIn = $passwordResetExpiresIn;
        $this->managerRegistry = $managerRegistry;
        $this->passwordResetLimiter = $passwordResetLimiter;
        $this->requestStack = $requestStack;
        $this->validator = $validator;
    }

    /**
     * @inheritDoc
     */
    public function transform($object, string $to, array $context = []): User
    {
        if (!$object instanceof UserPasswordTokenInput) {
            throw new \RuntimeException(sprintf('Cannot transform %s to %s', get_class($object), $to));
        }

        $user = $this->managerRegistry
            ->getRepository(User::class)
            ->findOneByConfirmationToken($object->token);

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
            $user->isEnabled()
            && $user->isAccountNonExpired()
            && $user->isAccountNonLocked()
        ) {
            $expiresAt = clone $user->getPasswordRequestedAt();
            $expiresAt->add(new \DateInterval(sprintf('PT%dS', $this->passwordResetExpiresIn)));

            if ($expiresAt <= new \DateTime()) {
                throw new UnprocessableEntityHttpException('Token is not valid anymore.');
            }

            $user->setPlainPassword($object->plainPassword);
            $this->validator->validate($user);

            $user->setPasswordRequestedAt(null);
            $user->setConfirmationToken(null);
            return $user;
        }

        throw new UnprocessableEntityHttpException('User is disabled, locked or expired.');
    }

    /**
     * @inheritDoc
     */
    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        if ($data instanceof UserInterface) {
            return false;
        }

        return User::class === $to && UserPasswordTokenInput::class === ($context['input']['class'] ?? null);
    }
}
