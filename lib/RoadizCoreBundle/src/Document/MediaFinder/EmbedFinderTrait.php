<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Document\MediaFinder;

use Doctrine\Persistence\ObjectManager;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\DocumentTranslation;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\Documents\Exceptions\APINeedsAuthentificationException;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;

trait EmbedFinderTrait
{
    protected function documentExists(ObjectManager $objectManager, string $embedId, ?string $embedPlatform): bool
    {
        $existingDocument = $objectManager->getRepository(Document::class)
            ->findOneBy([
                'embedId' => $embedId,
                'embedPlatform' => $embedPlatform,
            ]);

        return null !== $existingDocument;
    }

    protected function injectMetaInDocument(ObjectManager $objectManager, DocumentInterface $document): DocumentInterface
    {
        $translations = $objectManager->getRepository(Translation::class)->findAll();

        try {
            /** @var Translation $translation */
            foreach ($translations as $translation) {
                $documentTr = null;
                if ($document instanceof Document) {
                    $documentTr = $document->getDocumentTranslationsByTranslation($translation)->first() ?: null;
                    if (null === $documentTr) {
                        $documentTr = new DocumentTranslation();
                        $documentTr->setDocument($document);
                        $documentTr->setTranslation($translation);
                        // Need to inject translation before flushing to allow fetching existing translation
                        // from collection : line 43
                        $document->addDocumentTranslation($documentTr);
                        $objectManager->persist($documentTr);
                    }
                    $documentTr->setName($this->getMediaTitle());
                    $documentTr->setDescription($this->getMediaDescription());
                    $documentTr->setCopyright($this->getMediaCopyright());
                }
            }
        } catch (APINeedsAuthentificationException $exception) {
            // do not prevent from creating document if credentials are not provided.
        } catch (ClientExceptionInterface $exception) {
            // do not prevent from creating document if platform has errors, such as
            // too much API usage.
        }

        return $document;
    }
}
