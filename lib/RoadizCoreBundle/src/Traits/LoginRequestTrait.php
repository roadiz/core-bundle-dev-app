<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Traits;

use Doctrine\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Security\User\UserViewer;
use RZ\Roadiz\Random\TokenGenerator;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Trait LoginRequestTrait.
 *
 * This trait MUST be used in Controllers ONLY.
 */
trait LoginRequestTrait
{
    abstract protected function getUserViewer(): UserViewer;

    /**
     * @param FormInterface $form
     * @param ObjectManager $entityManager
     * @param LoggerInterface $logger
     * @param UrlGeneratorInterface $urlGenerator
     * @param string $resetRoute
     *
     * @return bool TRUE if confirmation has been sent. FALSE if errors
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function sendConfirmationEmail(
        FormInterface $form,
        ObjectManager $entityManager,
        LoggerInterface $logger,
        UrlGeneratorInterface $urlGenerator,
        string $resetRoute = 'loginResetPage'
    ): bool {
        $email = $form->get('email')->getData();
        /** @var User $user */
        $user = $entityManager->getRepository(User::class)->findOneByEmail($email);

        if (null !== $user) {
            if (!$user->isPasswordRequestNonExpired(User::CONFIRMATION_TTL)) {
                try {
                    $tokenGenerator = new TokenGenerator($logger);
                    $user->setPasswordRequestedAt(new \DateTime());
                    $user->setConfirmationToken($tokenGenerator->generateToken());
                    $entityManager->flush();
                    $userViewer = $this->getUserViewer();
                    $userViewer->sendPasswordResetLink($user, $resetRoute);
                    return true;
                } catch (\Throwable $e) {
                    $user->setPasswordRequestedAt(null);
                    $user->setConfirmationToken(null);
                    $entityManager->flush();
                    $logger->error($e->getMessage());
                    $form->addError(new FormError($e->getMessage()));
                }
            } else {
                $form->addError(new FormError('a.confirmation.email.has.already.be.sent'));
            }
        }

        return false;
    }
}
