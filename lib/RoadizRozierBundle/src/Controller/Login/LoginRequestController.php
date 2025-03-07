<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Login;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Form\LoginRequestForm;
use RZ\Roadiz\CoreBundle\Security\User\UserViewer;
use RZ\Roadiz\CoreBundle\Traits\LoginRequestTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class LoginRequestController extends AbstractController
{
    use LoginRequestTrait;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly UserViewer $userViewer,
        private readonly ManagerRegistry $managerRegistry,
    ) {
    }

    protected function getUserViewer(): UserViewer
    {
        return $this->userViewer;
    }

    public function indexAction(Request $request): Response
    {
        $form = $this->createForm(LoginRequestForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->sendConfirmationEmail(
                    $form,
                    $this->managerRegistry->getManagerForClass(User::class),
                    $this->logger,
                    $this->urlGenerator
                );
            }

            /*
             * Always go to confirm even if email is not valid
             * for avoiding database sniffing.
             */
            return $this->redirectToRoute(
                'loginRequestConfirmPage'
            );
        }

        return $this->render('@RoadizRozier/login/request.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function confirmAction(): Response
    {
        return $this->render('@RoadizRozier/login/requestConfirm.html.twig');
    }
}
