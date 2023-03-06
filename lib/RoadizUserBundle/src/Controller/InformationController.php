<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\Controller;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

final class InformationController
{
    private Security $security;

    /**
     * @param Security $security
     */
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function __invoke(): UserInterface
    {
        $user = $this->security->getUser();

        if (null === $user) {
            throw new NotFoundHttpException('No user found in request');
        }

        return $user;
    }
}
