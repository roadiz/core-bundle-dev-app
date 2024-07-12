<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Login;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Form\LoginRequestForm;
use RZ\Roadiz\CoreBundle\Security\User\UserViewer;
use RZ\Roadiz\CoreBundle\Traits\LoginRequestTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Themes\Rozier\RozierApp;
use Twig\Error\RuntimeError;

final class LoginRequestController extends RozierApp
{
    use LoginRequestTrait;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly UserViewer $userViewer
    ) {
    }

    protected function getUserViewer(): UserViewer
    {
        return $this->userViewer;
    }

    /**
     * @param Request $request
     *
     * @return Response
     * @throws RuntimeError
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function indexAction(Request $request): Response
    {
        $form = $this->createForm(LoginRequestForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->sendConfirmationEmail(
                    $form,
                    $this->em(),
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

        $this->assignation['form'] = $form->createView();

        return $this->render('@RoadizRozier/login/request.html.twig', $this->assignation);
    }

    /**
     * @return Response
     * @throws RuntimeError
     */
    public function confirmAction(): Response
    {
        return $this->render('@RoadizRozier/login/requestConfirm.html.twig', $this->assignation);
    }
}
