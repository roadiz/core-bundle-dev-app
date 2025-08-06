<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Document;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Security\LogTrail;
use RZ\Roadiz\Documents\Events\DocumentUpdatedEvent;
use RZ\Roadiz\RozierBundle\Form\DocumentLimitationsType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class DocumentLimitationController extends AbstractController
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly TranslatorInterface $translator,
        private readonly LogTrail $logTrail,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function limitationsAction(Request $request, Document $document): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCUMENTS_LIMITATIONS');

        $form = $this->createForm(DocumentLimitationsType::class, $document, [
            'referer' => $request->get('referer'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $msg = $this->translator->trans('document.%name%.updated', [
                '%name%' => (string) $document,
            ]);
            $this->logTrail->publishConfirmMessage($request, $msg);
            $this->eventDispatcher->dispatch(
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

        $assignation = [];
        $assignation['document'] = $document;
        $assignation['rawDocument'] = $document->getRawDocument();
        $assignation['form'] = $form->createView();

        return $this->render('@RoadizRozier/documents/limitations.html.twig', $assignation);
    }
}
