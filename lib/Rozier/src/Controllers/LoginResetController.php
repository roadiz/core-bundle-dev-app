<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers;

use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Form\LoginResetForm;
use RZ\Roadiz\CoreBundle\Traits\LoginResetTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Themes\Rozier\RozierApp;

class LoginResetController extends RozierApp
{
    use LoginResetTrait;

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function resetAction(Request $request, string $token): Response
    {
        /** @var User|null $user */
        $user = $this->getUserByToken($this->em(), $token);

        if (null !== $user) {
            $form = $this->createForm(LoginResetForm::class, null, [
                'token' => $token,
                'confirmationTtl' => User::CONFIRMATION_TTL,
            ]);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                if ($this->updateUserPassword($form, $user, $this->em())) {
                    return $this->redirectToRoute(
                        'loginResetConfirmPage'
                    );
                }
            }
            $this->assignation['form'] = $form->createView();
        } else {
            $this->assignation['error'] = $this->getTranslator()->trans('confirmation.token.is.invalid');
        }

        return $this->render('@RoadizRozier/login/reset.html.twig', $this->assignation);
    }

    public function confirmAction(): Response
    {
        return $this->render('@RoadizRozier/login/resetConfirm.html.twig', $this->assignation);
    }
}
