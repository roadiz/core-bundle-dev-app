<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Document;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\AttributeDocuments;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\TagTranslationDocuments;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

final class DocumentUsageController extends AbstractController
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
    ) {
    }

    /**
     * Return an object list using this document.
     */
    public function usageAction(Request $request, int $documentId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCUMENTS');

        $document = $this->managerRegistry
            ->getRepository(Document::class)
            ->find($documentId);

        if (!$document instanceof Document) {
            throw new ResourceNotFoundException();
        }

        return $this->render('@RoadizRozier/documents/usage.html.twig', [
            'document' => $document,
            'usages' => $document->getNodesSourcesByFields(),
            'attributes' => $document->getAttributeDocuments()
                ->map(fn (AttributeDocuments $attributeDocument) => $attributeDocument->getAttribute()),
            'tags' => $document->getTagTranslations()
                ->map(fn (TagTranslationDocuments $tagTranslationDocuments) => $tagTranslationDocuments->getTagTranslation()->getTag()),
        ]);
    }
}
