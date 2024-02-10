<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine\Indexer;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Exception\SolrServerNotAvailableException;
use RZ\Roadiz\CoreBundle\SearchEngine\ClientRegistry;
use RZ\Roadiz\CoreBundle\SearchEngine\SolariumFactoryInterface;
use Solarium\Core\Client\Client;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class AbstractIndexer implements CliAwareIndexer
{
    protected LoggerInterface $logger;
    protected ?SymfonyStyle $io = null;

    public function __construct(
        protected readonly ClientRegistry $clientRegistry,
        protected readonly ManagerRegistry $managerRegistry,
        protected readonly SolariumFactoryInterface $solariumFactory,
        readonly LoggerInterface $searchEngineLogger
    ) {
        $this->logger = $searchEngineLogger;
    }

    /**
     * @return Client
     */
    public function getSolr(): Client
    {
        $solr = $this->clientRegistry->getClient();
        if (null === $solr) {
            throw new SolrServerNotAvailableException();
        }
        return $solr;
    }

    /**
     * @param SymfonyStyle|null $io
     * @return AbstractIndexer
     */
    public function setIo(?SymfonyStyle $io): self
    {
        $this->io = $io;
        return $this;
    }

    /**
     * Empty Solr index.
     *
     * @param string|null $documentType
     */
    public function emptySolr(?string $documentType = null): void
    {
        $update = $this->getSolr()->createUpdate();
        if (null !== $documentType) {
            $update->addDeleteQuery(sprintf('document_type_s:"%s"', trim($documentType)));
        } else {
            // Delete ALL index
            $update->addDeleteQuery('*:*');
        }
        $update->addCommit(false, true, true);
        $this->getSolr()->update($update);
    }

    /**
     * Send an optimize and commit update query to Solr.
     */
    public function optimizeSolr(): void
    {
        $optimizeUpdate = $this->getSolr()->createUpdate();
        $optimizeUpdate->addOptimize(true, true);
        $this->getSolr()->update($optimizeUpdate);

        $this->commitSolr();
    }

    public function commitSolr(): void
    {
        $finalCommitUpdate = $this->getSolr()->createUpdate();
        $finalCommitUpdate->addCommit(true, true, false);
        $this->getSolr()->update($finalCommitUpdate);
    }
}
