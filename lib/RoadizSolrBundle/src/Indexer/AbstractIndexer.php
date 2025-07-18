<?php

declare(strict_types=1);

namespace RZ\Roadiz\SolrBundle\Indexer;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\SolrBundle\ClientRegistryInterface;
use RZ\Roadiz\SolrBundle\Exception\SolrServerNotConfiguredException;
use RZ\Roadiz\SolrBundle\Solarium\SolariumFactoryInterface;
use Solarium\Core\Client\Client;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class AbstractIndexer implements CliAwareIndexer
{
    protected ?SymfonyStyle $io = null;

    public function __construct(
        protected readonly ClientRegistryInterface $clientRegistry,
        protected readonly ManagerRegistry $managerRegistry,
        protected readonly SolariumFactoryInterface $solariumFactory,
        protected readonly LoggerInterface $searchEngineLogger,
    ) {
    }

    public function getSolr(): Client
    {
        $solr = $this->clientRegistry->getClient();
        if (null === $solr) {
            throw new SolrServerNotConfiguredException();
        }

        return $solr;
    }

    #[\Override]
    public function setIo(?SymfonyStyle $io): self
    {
        $this->io = $io;

        return $this;
    }

    /**
     * Empty Solr index.
     */
    #[\Override]
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
    #[\Override]
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
