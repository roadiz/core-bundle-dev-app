<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\CustomForm;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\CustomForm;
use RZ\Roadiz\CoreBundle\Entity\CustomFormField;
use RZ\Roadiz\CoreBundle\Security\LogTrail;
use RZ\Roadiz\RozierBundle\Form\CustomFormFieldType;
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
use Twig\Error\RuntimeError;

#[AsController]
final class CustomFormFieldController extends AbstractController
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly TranslatorInterface $translator,
        private readonly LogTrail $logTrail,
    ) {
    }

    public function listAction(int $customFormId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_CUSTOMFORMS');

        $customForm = $this->managerRegistry->getRepository(CustomForm::class)->find($customFormId);

        if (null === $customForm) {
            throw new ResourceNotFoundException();
        }

        return $this->render('@RoadizRozier/custom-form-fields/list.html.twig', [
            'customForm' => $customForm,
            'fields' => $customForm->getFields(),
        ]);
    }

    public function editAction(Request $request, int $customFormFieldId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_CUSTOMFORMS');

        /** @var CustomFormField|null $field */
        $field = $this->managerRegistry
            ->getRepository(CustomFormField::class)
            ->find($customFormFieldId);

        if (null === $field) {
            throw new ResourceNotFoundException();
        }

        $form = $this->createForm(CustomFormFieldType::class, $field);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->managerRegistry->getManager()->flush();

            $msg = $this->translator->trans('customFormField.%name%.updated', ['%name%' => $field->getName()]);
            $this->logTrail->publishConfirmMessage($request, $msg, $field);

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

        return $this->render('@RoadizRozier/custom-form-fields/edit.html.twig', [
            'field' => $field,
            'form' => $form->createView(),
            'customForm' => $field->getCustomForm(),
        ]);
    }

    public function addAction(Request $request, int $customFormId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_CUSTOMFORMS');

        $customForm = $this->managerRegistry
            ->getRepository(CustomForm::class)
            ->find($customFormId);

        if (null === $customForm) {
            throw new ResourceNotFoundException();
        }

        $field = new CustomFormField();
        $field->setCustomForm($customForm);

        $form = $this->createForm(CustomFormFieldType::class, $field);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->managerRegistry->getManager()->persist($field);
                $this->managerRegistry->getManager()->flush();

                $msg = $this->translator->trans(
                    'customFormField.%name%.created',
                    ['%name%' => $field->getName()]
                );
                $this->logTrail->publishConfirmMessage($request, $msg, $field);

                /*
                 * Redirect to update schema page
                 */
                return $this->redirectToRoute(
                    'customFormFieldsListPage',
                    [
                        'customFormId' => $customFormId,
                    ]
                );
            } catch (\Exception $e) {
                $this->logTrail->publishErrorMessage($request, $e->getMessage(), $field);

                /*
                 * Redirect to add page
                 */
                return $this->redirectToRoute(
                    'customFormFieldsAddPage',
                    ['customFormId' => $customFormId]
                );
            }
        }

        return $this->render('@RoadizRozier/custom-form-fields/add.html.twig', [
            'customForm' => $customForm,
            'form' => $form->createView(),
            'field' => $field,
        ]);
    }

    /**
     * Return a deletion form for requested node.
     *
     * @throws RuntimeError
     */
    public function deleteAction(Request $request, int $customFormFieldId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_CUSTOMFORMS_DELETE');

        $field = $this->managerRegistry
            ->getRepository(CustomFormField::class)
            ->find($customFormFieldId);

        if (null === $field) {
            throw new ResourceNotFoundException();
        }

        $form = $this->buildDeleteForm($field);
        $form->handleRequest($request);

        if (
            $form->isSubmitted()
            && $form->isValid()
            && $form->getData()['customFormFieldId'] == $field->getId()
        ) {
            $customFormId = $field->getCustomForm()->getId();

            $this->managerRegistry->getManager()->remove($field);
            $this->managerRegistry->getManager()->flush();

            /*
             * Update Database
             */
            $msg = $this->translator->trans(
                'customFormField.%name%.deleted',
                ['%name%' => $field->getName()]
            );
            $this->logTrail->publishConfirmMessage($request, $msg);

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

        return $this->render('@RoadizRozier/custom-form-fields/delete.html.twig', [
            'field' => $field,
            'form' => $form->createView(),
        ]);
    }

    private function buildDeleteForm(CustomFormField $field): FormInterface
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
