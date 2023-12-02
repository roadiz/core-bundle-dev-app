<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers\CustomForms;

use RZ\Roadiz\CoreBundle\Entity\CustomForm;
use RZ\Roadiz\CoreBundle\Entity\CustomFormAnswer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Themes\Rozier\RozierApp;
use Twig\Error\RuntimeError;

class CustomFormAnswersController extends RozierApp
{
    /**
     * List every node-types.
     *
     * @param Request $request
     * @param int $customFormId
     *
     * @return Response
     * @throws RuntimeError
     */
    public function listAction(Request $request, int $customFormId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_CUSTOMFORMS');
        /*
         * Manage get request to filter list
         */

        $customForm = $this->em()->find(
            CustomForm::class,
            $customFormId
        );

        $listManager = $this->createEntityListManager(
            CustomFormAnswer::class,
            ["customForm" => $customForm],
            ["submittedAt" => "DESC"]
        );
        $listManager->setDisplayingNotPublishedNodes(true);
        $listManager->handle();
        $this->assignation['customForm'] = $customForm;
        $this->assignation['filters'] = $listManager->getAssignation();
        $this->assignation['custom_form_answers'] = $listManager->getEntities();

        return $this->render('@RoadizRozier/custom-form-answers/list.html.twig', $this->assignation);
    }

    /**
     * Return a deletion form for requested node-type.
     *
     * @param Request $request
     * @param int $customFormAnswerId
     *
     * @return Response
     * @throws RuntimeError
     */
    public function deleteAction(Request $request, int $customFormAnswerId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_CUSTOMFORMS_DELETE');

        $customFormAnswer = $this->em()->find(CustomFormAnswer::class, $customFormAnswerId);

        if (null === $customFormAnswer) {
            throw new ResourceNotFoundException();
        }

        $this->assignation['customFormAnswer'] = $customFormAnswer;
        $form = $this->buildDeleteForm($customFormAnswer);
        $form->handleRequest($request);

        if (
            $form->isSubmitted() &&
            $form->isValid()
        ) {
            $this->em()->remove($customFormAnswer);
            $this->em()->flush();

            $msg = $this->getTranslator()->trans('customFormAnswer.%id%.deleted', ['%id%' => $customFormAnswer->getId()]);
            $this->publishConfirmMessage($request, $msg, $customFormAnswer);
            /*
             * Redirect to update schema page
             */
            return $this->redirectToRoute(
                'customFormAnswersHomePage',
                ["customFormId" => $customFormAnswer->getCustomForm()->getId()]
            );
        }

        $this->assignation['form'] = $form->createView();

        return $this->render('@RoadizRozier/custom-form-answers/delete.html.twig', $this->assignation);
    }

    /**
     * @param CustomFormAnswer $customFormAnswer
     *
     * @return FormInterface
     */
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
