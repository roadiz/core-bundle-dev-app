<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Document;

use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\Documents\DocumentFinderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DocumentPreviewController extends AbstractController
{
    private DocumentFinderInterface $documentFinder;

    public function __construct(DocumentFinderInterface $documentFinder)
    {
        $this->documentFinder = $documentFinder;
    }

    public function previewAction(Request $request, Document $documentId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCUMENTS');

        $document = $documentId;
        $assignation = [];
        $assignation['document'] = $document;
        $assignation['thumbnailFormat'] = [
            'width' => 750,
            'controls' => true,
            'srcset' => [
                [
                    'format' => [
                        'width' => 480,
                        'quality' => 80,
                    ],
                    'rule' => '480w',
                ],
                [
                    'format' => [
                        'width' => 768,
                        'quality' => 80,
                    ],
                    'rule' => '768w',
                ],
                [
                    'format' => [
                        'width' => 1400,
                        'quality' => 80,
                    ],
                    'rule' => '1400w',
                ],
            ],
            'sizes' => [
                '(min-width: 1380px) 1200px',
                '(min-width: 768px) 768px',
                '(min-width: 480px) 480px',
            ],
        ];

        $otherVideos = $this->documentFinder->findVideosWithFilename($document->getFilename());
        $otherAudios = $this->documentFinder->findAudiosWithFilename($document->getFilename());
        $otherPictures = $this->documentFinder->findPicturesWithFilename($document->getFilename());

        $assignation['otherVideos'] = $otherVideos;
        $assignation['otherAudios'] = $otherAudios;
        $assignation['otherPictures'] = $otherPictures;
        $assignation['thumbnailFormat']['picture'] = true;
        $assignation['infos'] = [];
        if ($document->isProcessable() || $document->isSvg()) {
            $assignation['infos']['width'] = $document->getImageWidth().'px';
            $assignation['infos']['height'] = $document->getImageHeight().'px';
        }
        if ($document->getMediaDuration() > 0) {
            $assignation['infos']['duration'] = $document->getMediaDuration().' sec';
        }

        return $this->render('@RoadizRozier/documents/preview.html.twig', $assignation);
    }
}
