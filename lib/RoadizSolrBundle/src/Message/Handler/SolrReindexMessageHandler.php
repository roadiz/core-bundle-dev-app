<?php

declare(strict_types=1);

namespace RZ\Roadiz\SolrBundle\Message\Handler;

use Psr\Log\LoggerInterface;
use RZ\Roadiz\SolrBundle\Exception\SolrServerException;
use RZ\Roadiz\SolrBundle\Indexer\IndexerFactoryInterface;
use RZ\Roadiz\SolrBundle\Message\SolrReindexMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class SolrReindexMessageHandler
{
    public function __construct(
        private IndexerFactoryInterface $indexerFactory,
        private LoggerInterface $searchEngineLogger,
    ) {
    }

    public function __invoke(SolrReindexMessage $message): void
    {
        try {
            if (!empty($message->getIdentifier())) {
                // Cannot typehint with class-string: breaks Symfony Serializer 5.4
                // @phpstan-ignore-next-line
                $this->indexerFactory->getIndexerFor($message->getClassname())->index($message->getIdentifier());
            }
        } catch (SolrServerException) {
            return;
        } catch (\LogicException $exception) {
            $this->searchEngineLogger->error($exception->getMessage());
        }
    }
}
