<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers\CustomForms;

use PhpOffice\PhpSpreadsheet\Exception;
use RZ\Roadiz\CoreBundle\Entity\CustomForm;
use RZ\Roadiz\CoreBundle\Entity\CustomFormAnswer;
use RZ\Roadiz\CoreBundle\CustomForm\CustomFormAnswerSerializer;
use RZ\Roadiz\CoreBundle\Xlsx\XlsxExporter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Themes\Rozier\RozierApp;

class CustomFormsUtilsController extends RozierApp
{
    public function __construct(private readonly CustomFormAnswerSerializer $customFormAnswerSerializer)
    {
    }

    /**
     * Export all custom form's answer in a Xlsx file (.rzt).
     *
     * @param Request $request
     * @param int $id
     *
     * @return Response
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function exportAction(Request $request, int $id): Response
    {
        /** @var CustomForm|null $customForm */
        $customForm = $this->em()->find(CustomForm::class, $id);
        if (null === $customForm) {
            throw $this->createNotFoundException();
        }

        $answers = $customForm->getCustomFormAnswers();

        /**
         * @var int $key
         * @var CustomFormAnswer $answer
         */
        foreach ($answers as $key => $answer) {
            $array = array_merge(
                [$answer->getIp(), $answer->getSubmittedAt()],
                $this->customFormAnswerSerializer->toSimpleArray($answer)
            );
            $answers[$key] = $array;
        }

        $keys = ["ip", "submitted.date"];

        $fields = $customForm->getFieldsLabels();
        $keys = array_merge($keys, $fields);

        $exporter = new XlsxExporter($this->getTranslator());
        $xlsx = $exporter->exportXlsx($answers, $keys);

        $response = new Response(
            $xlsx,
            Response::HTTP_OK,
            []
        );

        $response->headers->set(
            'Content-Disposition',
            $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $customForm->getName() . '.xlsx'
            )
        );

        $response->prepare($request);

        return $response;
    }

    /**
     * Duplicate custom form by ID
     *
     * @param Request $request
     * @param int $id
     *
     * @return Response
     */
    public function duplicateAction(Request $request, int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_CUSTOMFORMS');
        /** @var CustomForm|null $existingCustomForm */
        $existingCustomForm = $this->em()->find(CustomForm::class, $id);

        if (null === $existingCustomForm) {
            throw $this->createNotFoundException();
        }

        try {
            $newCustomForm = clone $existingCustomForm;
            $newCustomForm->setCreatedAt(new \DateTime());
            $newCustomForm->setUpdatedAt(new \DateTime());
            $em = $this->em();

            foreach ($newCustomForm->getFields() as $field) {
                $em->persist($field);
            }

            $em->persist($newCustomForm);
            $em->flush();

            $msg = $this->getTranslator()->trans("duplicated.custom.form.%name%", [
                '%name%' => $existingCustomForm->getDisplayName(),
            ]);

            $this->publishConfirmMessage($request, $msg, $newCustomForm);

            return $this->redirectToRoute(
                'customFormsEditPage',
                ["id" => $newCustomForm->getId()]
            );
        } catch (\Exception $e) {
            $this->publishErrorMessage(
                $request,
                $this->getTranslator()->trans("impossible.duplicate.custom.form.%name%", [
                    '%name%' => $existingCustomForm->getDisplayName(),
                ]),
                $newCustomForm
            );
            $this->publishErrorMessage($request, $e->getMessage(), $existingCustomForm);

            return $this->redirectToRoute(
                'customFormsEditPage',
                ["id" => $existingCustomForm->getId()]
            );
        }
    }
}
