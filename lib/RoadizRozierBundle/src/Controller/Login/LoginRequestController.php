<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Login;

use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Form\LoginRequestForm;
use RZ\Roadiz\CoreBundle\Security\User\UserViewer;
use RZ\Roadiz\CoreBundle\Traits\LoginRequestTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Themes\Rozier\RozierApp;

final class LoginRequestController extends RozierApp
{
    use LoginRequestTrait;

    private LoggerInterface $logger;
    private UrlGeneratorInterface $urlGenerator;
    private UserViewer $userViewer;

    public function __construct(LoggerInterface $logger, UrlGeneratorInterface $urlGenerator, UserViewer $userViewer)
    {
        $this->logger = $logger;
        $this->urlGenerator = $urlGenerator;
        $this->userViewer = $userViewer;
    }

    protected function getUserViewer(): UserViewer
    {
        return $this->userViewer;
    }

    /**
     * @param Request $request
     *
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function indexAction(Request $request)
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
     */
    public function confirmAction()
    {
        return $this->render('@RoadizRozier/login/requestConfirm.html.twig', $this->assignation);
    }
}
