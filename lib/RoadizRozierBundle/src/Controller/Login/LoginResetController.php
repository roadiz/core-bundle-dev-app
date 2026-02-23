<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Login;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Form\LoginResetForm;
use RZ\Roadiz\CoreBundle\Traits\LoginResetTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
final class LoginResetController extends AbstractController
{
    use LoginResetTrait;

    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route(
        path: '/rz-admin/login/reset/{token}',
        name: 'loginResetPage',
        requirements: ['token' => '[^\/]+'],
        methods: ['GET', 'POST'],
    )]
    public function resetAction(Request $request, string $token): Response
    {
        /** @var User|null $user */
        $user = $this->getUserByToken($this->managerRegistry->getManager(), $token);
        $assignation = [];

        if (null !== $user) {
            $form = $this->createForm(LoginResetForm::class, null, [
                'token' => $token,
                'confirmationTtl' => User::CONFIRMATION_TTL,
            ]);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                if ($this->updateUserPassword($form, $user, $this->managerRegistry->getManager())) {
                    return $this->redirectToRoute(
                        'loginResetConfirmPage'
                    );
                }
            }
            $assignation['form'] = $form->createView();
        } else {
            $assignation['error'] = $this->translator->trans('confirmation.token.is.invalid');
        }

        return $this->render('@RoadizRozier/login/reset.html.twig', $assignation);
    }

    #[Route(
        path: '/rz-admin/login/reset/confirm',
        name: 'loginResetConfirmPage',
        methods: ['GET'],
        priority: 2,
    )]
    public function confirmAction(): Response
    {
        return $this->render('@RoadizRozier/login/resetConfirm.html.twig');
    }
}
