<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Document;

use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\Documents\DocumentFinderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Themes\Rozier\RozierApp;

class DocumentPreviewController extends RozierApp
{
    private DocumentFinderInterface $documentFinder;

    /**
     * @param DocumentFinderInterface $documentFinder
     */
    public function __construct(DocumentFinderInterface $documentFinder)
    {
        $this->documentFinder = $documentFinder;
    }

    /**
     * @param Request $request
     * @param Document $documentId
     *
     * @return Response
     * @throws \Twig\Error\RuntimeError
     */
    public function previewAction(Request $request, Document $documentId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCUMENTS');

        $document = $documentId;

        $this->assignation['document'] = $document;
        $this->assignation['thumbnailFormat'] = [
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

        $this->assignation['otherVideos'] = $otherVideos;
        $this->assignation['otherAudios'] = $otherAudios;
        $this->assignation['otherPictures'] = $otherPictures;
        $this->assignation['thumbnailFormat']['picture'] = true;
        $this->assignation['infos'] = [];
        if ($document->isProcessable() || $document->isSvg()) {
            $this->assignation['infos']['width'] = $document->getImageWidth() . 'px';
            $this->assignation['infos']['height'] = $document->getImageHeight() . 'px';
        }
        if ($document->getMediaDuration() > 0) {
            $this->assignation['infos']['duration'] = $document->getMediaDuration() . ' sec';
        }

        return $this->render('@RoadizRozier/documents/preview.html.twig', $this->assignation);
    }
}
