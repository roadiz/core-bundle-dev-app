<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Security\Authentication;

use Doctrine\Persistence\ManagerRegistry;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler;
use RZ\Roadiz\CoreBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

final class JwtAuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(
        private readonly AuthenticationSuccessHandler $decorated,
        private readonly ManagerRegistry $managerRegistry
    ) {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        $response = $this->decorated->onAuthenticationSuccess($request, $token);
        $user = $token->getUser();

        if ($user instanceof User) {
            $user->setLastLogin(new \DateTime());
            $this->managerRegistry->getManager()->flush();
        }
        return $response;
    }
}
