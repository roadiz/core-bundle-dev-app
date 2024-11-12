<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Document;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\Documents\Events\DocumentUpdatedEvent;
use RZ\Roadiz\RozierBundle\Form\DocumentLimitationsType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Themes\Rozier\RozierApp;

final class DocumentLimitationsController extends RozierApp
{
    public function __construct(private readonly ManagerRegistry $managerRegistry)
    {
    }

    public function limitationsAction(Request $request, Document $document): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCUMENTS_LIMITATIONS');

        $form = $this->createForm(DocumentLimitationsType::class, $document, [
            'referer' => $request->get('referer'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $msg = $this->getTranslator()->trans('document.%name%.updated', [
                '%name%' => (string) $document,
            ]);
            $this->publishConfirmMessage($request, $msg);
            $this->dispatchEvent(
                new DocumentUpdatedEvent($document)
            );

            $this->managerRegistry->getManager()->flush();

            return $this->redirectToRoute(
                'documentsLimitationsPage',
                [
                    'id' => $document->getId(),
                ]
            );
        }

        $this->assignation['document'] = $document;
        $this->assignation['rawDocument'] = $document->getRawDocument();
        $this->assignation['form'] = $form->createView();

        return $this->render('@RoadizRozier/documents/limitations.html.twig', $this->assignation);
    }
}
