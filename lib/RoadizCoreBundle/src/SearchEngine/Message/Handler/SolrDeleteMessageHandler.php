<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine\Message\Handler;

use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Exception\SolrServerNotAvailableException;
use RZ\Roadiz\CoreBundle\SearchEngine\Indexer\IndexerFactoryInterface;
use RZ\Roadiz\CoreBundle\SearchEngine\Message\SolrDeleteMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class SolrDeleteMessageHandler implements MessageHandlerInterface
{
    private LoggerInterface $logger;
    private IndexerFactoryInterface $indexerFactory;

    public function __construct(IndexerFactoryInterface $indexerFactory, LoggerInterface $searchEngineLogger)
    {
        $this->logger = $searchEngineLogger;
        $this->indexerFactory = $indexerFactory;
    }

    public function __invoke(SolrDeleteMessage $message): void
    {
        try {
            if (!empty($message->getIdentifier())) {
                // Cannot typehint with class-string: breaks Symfony Serializer 5.4
                // @phpstan-ignore-next-line
                $this->indexerFactory->getIndexerFor($message->getClassname())->delete($message->getIdentifier());
            }
        } catch (SolrServerNotAvailableException $exception) {
            return;
        } catch (\LogicException $exception) {
            $this->logger->error($exception->getMessage());
        }
    }
}
