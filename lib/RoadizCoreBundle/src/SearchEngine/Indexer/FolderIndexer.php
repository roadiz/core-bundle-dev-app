<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine\Indexer;

use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\Folder;
use Solarium\Exception\HttpException;

final class FolderIndexer extends DocumentIndexer
{
    #[\Override]
    public function index(mixed $id): void
    {
        try {
            $folder = $this->managerRegistry->getRepository(Folder::class)->find($id);
            if (null === $folder) {
                return;
            }
            $update = $this->getSolr()->createUpdate();
            $documents = $folder->getDocuments();

            foreach ($documents as $document) {
                if ($document instanceof Document) {
                    foreach ($document->getDocumentTranslations() as $documentTranslation) {
                        $solarium = $this->solariumFactory->createWithDocumentTranslation($documentTranslation);
                        $solarium->getDocumentFromIndex();
                        $solarium->update($update);
                    }
                }
            }
            $this->getSolr()->update($update);

            // then optimize
            $optimizeUpdate = $this->getSolr()->createUpdate();
            $optimizeUpdate->addOptimize(true, true, 5);
            $this->getSolr()->update($optimizeUpdate);
            // and commit
            $finalCommitUpdate = $this->getSolr()->createUpdate();
            $finalCommitUpdate->addCommit(true, true, false);
            $this->getSolr()->update($finalCommitUpdate);
        } catch (HttpException $exception) {
            $this->logger->error($exception->getMessage());
        }
    }

    #[\Override]
    public function delete(mixed $id): void
    {
        // Just reindex all linked documents to get rid of folder
        $this->index($id);
    }
}
