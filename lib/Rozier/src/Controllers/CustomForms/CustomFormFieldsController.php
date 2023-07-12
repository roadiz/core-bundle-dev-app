<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers\CustomForms;

use Exception;
use RZ\Roadiz\CoreBundle\Entity\CustomForm;
use RZ\Roadiz\CoreBundle\Entity\CustomFormField;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Themes\Rozier\Forms\CustomFormFieldType;
use Themes\Rozier\RozierApp;
use Twig\Error\RuntimeError;

class CustomFormFieldsController extends RozierApp
{
    /**
     * List every node-type-fields.
     *
     * @param Request $request
     * @param int     $customFormId
     *
     * @return Response
     */
    public function listAction(Request $request, int $customFormId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_CUSTOMFORMS');

        $customForm = $this->em()->find(CustomForm::class, $customFormId);

        if ($customForm !== null) {
            $fields = $customForm->getFields();

            $this->assignation['customForm'] = $customForm;
            $this->assignation['fields'] = $fields;

            return $this->render('@RoadizRozier/custom-form-fields/list.html.twig', $this->assignation);
        }

        throw new ResourceNotFoundException();
    }

    /**
     * Return an edition form for requested node-type.
     *
     * @param Request $request
     * @param int     $customFormFieldId
     *
     * @return Response
     */
    public function editAction(Request $request, int $customFormFieldId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_CUSTOMFORMS');

        /** @var CustomFormField|null $field */
        $field = $this->em()->find(CustomFormField::class, $customFormFieldId);

        if ($field !== null) {
            $this->assignation['customForm'] = $field->getCustomForm();
            $this->assignation['field'] = $field;
            $form = $this->createForm(CustomFormFieldType::class, $field);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->em()->flush();

                $msg = $this->getTranslator()->trans('customFormField.%name%.updated', ['%name%' => $field->getName()]);
                $this->publishConfirmMessage($request, $msg, $field);

                /*
                 * Redirect to update schema page
                 */
                return $this->redirectToRoute(
                    'customFormFieldsListPage',
                    [
                        'customFormId' => $field->getCustomForm()->getId(),
                    ]
                );
            }

            $this->assignation['form'] = $form->createView();

            return $this->render('@RoadizRozier/custom-form-fields/edit.html.twig', $this->assignation);
        }

        throw new ResourceNotFoundException();
    }

    /**
     * Return a creation form for requested node-type.
     *
     * @param Request $request
     * @param int $customFormId
     *
     * @return Response
     * @throws RuntimeError
     */
    public function addAction(Request $request, int $customFormId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_CUSTOMFORMS');

        $customForm = $this->em()->find(CustomForm::class, $customFormId);
        if ($customForm === null) {
            throw new ResourceNotFoundException();
        }

        $field = new CustomFormField();
        $field->setCustomForm($customForm);
        $this->assignation['customForm'] = $customForm;
        $this->assignation['field'] = $field;
        $form = $this->createForm(CustomFormFieldType::class, $field);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->em()->persist($field);
                $this->em()->flush();

                $msg = $this->getTranslator()->trans(
                    'customFormField.%name%.created',
                    ['%name%' => $field->getName()]
                );
                $this->publishConfirmMessage($request, $msg, $field);

                /*
                 * Redirect to update schema page
                 */
                return $this->redirectToRoute(
                    'customFormFieldsListPage',
                    [
                        'customFormId' => $customFormId,
                    ]
                );
            } catch (Exception $e) {
                $msg = $e->getMessage();
                $this->publishErrorMessage($request, $msg, $field);
                /*
                 * Redirect to add page
                 */
                return $this->redirectToRoute(
                    'customFormFieldsAddPage',
                    ['customFormId' => $customFormId]
                );
            }
        }

        $this->assignation['form'] = $form->createView();

        return $this->render('@RoadizRozier/custom-form-fields/add.html.twig', $this->assignation);
    }

    /**
     * Return a deletion form for requested node.
     *
     * @param Request $request
     * @param int     $customFormFieldId
     *
     * @return Response
     */
    public function deleteAction(Request $request, int $customFormFieldId)
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_CUSTOMFORMS_DELETE');

        $field = $this->em()->find(CustomFormField::class, $customFormFieldId);

        if ($field !== null) {
            $this->assignation['field'] = $field;
            $form = $this->buildDeleteForm($field);
            $form->handleRequest($request);

            if (
                $form->isSubmitted() &&
                $form->isValid() &&
                $form->getData()['customFormFieldId'] == $field->getId()
            ) {
                $customFormId = $field->getCustomForm()->getId();

                $this->em()->remove($field);
                $this->em()->flush();

                /*
                 * Update Database
                 */
                $msg = $this->getTranslator()->trans(
                    'customFormField.%name%.deleted',
                    ['%name%' => $field->getName()]
                );
                $this->publishConfirmMessage($request, $msg, $field);

                /*
                 * Redirect to update schema page
                 */
                return $this->redirectToRoute(
                    'customFormFieldsListPage',
                    [
                        'customFormId' => $customFormId,
                    ]
                );
            }

            $this->assignation['form'] = $form->createView();

            return $this->render('@RoadizRozier/custom-form-fields/delete.html.twig', $this->assignation);
        }

        throw new ResourceNotFoundException();
    }

    /**
     * @param CustomFormField $field
     *
     * @return FormInterface
     */
    private function buildDeleteForm(CustomFormField $field)
    {
        $builder = $this->createFormBuilder()
                        ->add('customFormFieldId', HiddenType::class, [
                            'data' => $field->getId(),
                            'constraints' => [
                                new NotNull(),
                                new NotBlank(),
                            ],
                        ]);

        return $builder->getForm();
    }
}
