<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\CustomForm;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\CustomForm\CustomFormAnswerSerializer;
use RZ\Roadiz\CoreBundle\Entity\CustomForm;
use RZ\Roadiz\CoreBundle\Entity\CustomFormAnswer;
use RZ\Roadiz\CoreBundle\Security\LogTrail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
final class CustomFormUtilsController extends AbstractController
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly TranslatorInterface $translator,
        private readonly CustomFormAnswerSerializer $customFormAnswerSerializer,
        private readonly SerializerInterface $serializer,
        private readonly LogTrail $logTrail,
        private readonly array $csvEncoderOptions,
    ) {
    }

    /**
     * Export all custom form's answers in a CSV file.
     */
    public function exportAction(Request $request, int $id): Response
    {
        $customForm = $this->managerRegistry->getRepository(CustomForm::class)->find($id);
        if (null === $customForm) {
            throw $this->createNotFoundException();
        }

        $query = $this->managerRegistry
            ->getRepository(CustomFormAnswer::class)
            ->createQueryBuilder('cfa')
            ->where('cfa.customForm = :customForm')
            ->setParameter('customForm', $customForm)
            ->orderBy('cfa.submittedAt', 'DESC')
            ->getQuery();

        $fields = $customForm->getFieldsLabels();
        $keys = [
            'ip',
            'submitted.date',
            ...$fields,
        ];

        $response = new StreamedResponse(function () use ($query, $keys) {
            $answersArray = [];
            foreach ($query->toIterable() as $row) {
                // do stuff with the data in the row
                $answersArray[] = $this->customFormAnswerSerializer->toSimpleArray($row);
            }

            echo $this->serializer->serialize($answersArray, 'csv', [
                ...$this->csvEncoderOptions,
                'csv_headers' => $keys,
            ]);
        });
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set(
            'Content-Disposition',
            $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $customForm->getName().'.csv'
            )
        );

        $response->prepare($request);

        return $response;
    }

    /**
     * Duplicate custom form by ID.
     */
    public function duplicateAction(Request $request, int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_CUSTOMFORMS');
        $existingCustomForm = $this->managerRegistry->getRepository(CustomForm::class)->find($id);
        if (null === $existingCustomForm) {
            throw $this->createNotFoundException();
        }

        try {
            $newCustomForm = clone $existingCustomForm;
            $newCustomForm->setCreatedAt(new \DateTime());
            $newCustomForm->setUpdatedAt(new \DateTime());
            $em = $this->managerRegistry->getManager();

            foreach ($newCustomForm->getFields() as $field) {
                $em->persist($field);
            }

            $em->persist($newCustomForm);
            $em->flush();

            $msg = $this->translator->trans('duplicated.custom.form.%name%', [
                '%name%' => $existingCustomForm->getDisplayName(),
            ]);

            $this->logTrail->publishConfirmMessage($request, $msg, $newCustomForm);

            return $this->redirectToRoute(
                'customFormsEditPage',
                ['id' => $newCustomForm->getId()]
            );
        } catch (\Exception $e) {
            $this->logTrail->publishErrorMessage(
                $request,
                $this->translator->trans('impossible.duplicate.custom.form.%name%', [
                    '%name%' => $existingCustomForm->getDisplayName(),
                ]),
                $newCustomForm
            );
            $this->logTrail->publishErrorMessage($request, $e->getMessage(), $existingCustomForm);

            return $this->redirectToRoute(
                'customFormsEditPage',
                ['id' => $existingCustomForm->getId()]
            );
        }
    }
}
