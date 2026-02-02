<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\CustomForm;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\CustomForm;
use RZ\Roadiz\CoreBundle\Entity\CustomFormAnswer;
use RZ\Roadiz\CoreBundle\ListManager\EntityListManagerFactoryInterface;
use RZ\Roadiz\CoreBundle\Security\LogTrail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
final class CustomFormAnswerController extends AbstractController
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly EntityListManagerFactoryInterface $entityListManagerFactory,
        private readonly TranslatorInterface $translator,
        private readonly LogTrail $logTrail,
    ) {
    }

    public function listAction(Request $request, int $customFormId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_CUSTOMFORMS');

        $customForm = $this->managerRegistry
            ->getRepository(CustomForm::class)
            ->find($customFormId);

        $listManager = $this->entityListManagerFactory->createAdminEntityListManager(
            CustomFormAnswer::class,
            ['customForm' => $customForm],
            ['submittedAt' => 'DESC']
        );
        $listManager->handle();

        return $this->render('@RoadizRozier/custom-form-answers/list.html.twig', [
            'customForm' => $customForm,
            'filters' => $listManager->getAssignation(),
            'custom_form_answers' => $listManager->getEntities(),
        ]);
    }

    public function deleteAction(Request $request, int $customFormAnswerId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_CUSTOMFORMS_DELETE');

        $customFormAnswer = $this->managerRegistry
            ->getRepository(CustomFormAnswer::class)
            ->find($customFormAnswerId);

        if (!$customFormAnswer instanceof CustomFormAnswer) {
            throw new ResourceNotFoundException();
        }

        $form = $this->buildDeleteForm($customFormAnswer);
        $form->handleRequest($request);

        if (
            $form->isSubmitted()
            && $form->isValid()
        ) {
            $this->managerRegistry->getManager()->remove($customFormAnswer);
            $this->managerRegistry->getManager()->flush();

            $msg = $this->translator->trans('customFormAnswer.%id%.deleted', ['%id%' => $customFormAnswer->getId()]);
            $this->logTrail->publishConfirmMessage($request, $msg, $customFormAnswer);

            /*
             * Redirect to update schema page
             */
            return $this->redirectToRoute(
                'customFormAnswersHomePage',
                ['customFormId' => $customFormAnswer->getCustomForm()->getId()]
            );
        }

        $title = $this->translator->trans(
            'delete.customFormAnswer.%name%',
            ['%name%' => $customFormAnswer->getCustomForm()->getDisplayName()]
        );

        return $this->render('@RoadizRozier/admin/confirm_action.html.twig', [
            'title' => $title,
            'headPath' => '@RoadizRozier/custom-forms/head.html.twig',
            'cancelPath' => $this->generateUrl('customFormAnswersHomePage', [
                'customFormId' => $customFormAnswer->getCustomForm()->getId(),
            ]),
            'alertMessage' => 'are_you_sure.delete.customFormAnswer',
            'form' => $form->createView(),
        ]);
    }

    private function buildDeleteForm(CustomFormAnswer $customFormAnswer): FormInterface
    {
        $builder = $this->createFormBuilder()
                        ->add('customFormAnswerId', HiddenType::class, [
                            'data' => $customFormAnswer->getId(),
                            'constraints' => [
                                new NotNull(),
                                new NotBlank(),
                            ],
                        ]);

        return $builder->getForm();
    }
}
