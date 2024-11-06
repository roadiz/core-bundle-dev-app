<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Traits;

use Doctrine\Persistence\ObjectManager;
use RZ\Roadiz\CoreBundle\Entity\User;
use Symfony\Component\Form\FormInterface;

trait LoginResetTrait
{
    public function getUserByToken(ObjectManager $entityManager, string $token): ?User
    {
        return $entityManager->getRepository(User::class)->findOneByConfirmationToken($token);
    }

    /**
     * @return bool
     */
    public function updateUserPassword(FormInterface $form, User $user, ObjectManager $entityManager)
    {
        $user->setConfirmationToken(null);
        $user->setPasswordRequestedAt(null);
        $user->setPlainPassword($form->get('plainPassword')->getData());
        /*
         * If user was forced to update its credentials,
         * we remove expiration.
         */
        if (!$user->isCredentialsNonExpired()) {
            if (null !== $user->getCredentialsExpiresAt()) {
                $user->setCredentialsExpiresAt(null);
            }
        }
        $entityManager->flush();

        return true;
    }
}
