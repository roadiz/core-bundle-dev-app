<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine\Indexer;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use Solarium\Exception\HttpException;
use Solarium\Plugin\BufferedAdd\BufferedAdd;
use Solarium\QueryType\Update\Query\Query as UpdateQuery;

class NodesSourcesIndexer extends AbstractIndexer implements BatchIndexer
{
    public function index(mixed $id): void
    {
        $update = $this->getSolr()->createUpdate();
        $this->indexNodeSource(
            $this->managerRegistry->getRepository(NodesSources::class)->find($id),
            $update
        );
        $update->addCommit(true, true, false);
        $this->getSolr()->update($update);
    }

    protected function indexNodeSource(?NodesSources $nodeSource, UpdateQuery $update): void
    {
        if (null !== $nodeSource) {
            try {
                $solrSource = $this->solariumFactory->createWithNodesSources($nodeSource);
                $solrSource->getDocumentFromIndex();
                $solrSource->update($update);
            } catch (HttpException $exception) {
                $this->logger->error($exception->getMessage());
            }
        }
    }

    public function delete(mixed $id): void
    {
        $this->deleteNodeSource($this->managerRegistry->getRepository(NodesSources::class)->find($id));
    }

    protected function deleteNodeSource(?NodesSources $nodeSource): void
    {
        if (null !== $nodeSource) {
            try {
                $solrSource = $this->solariumFactory->createWithNodesSources($nodeSource);
                $solrSource->getDocumentFromIndex();
                $solrSource->removeAndCommit();
            } catch (HttpException $exception) {
                $this->logger->error($exception->getMessage());
            }
        }
    }

    /**
     * Overridable.
     */
    protected function getAllQueryBuilder(): QueryBuilder
    {
        return $this->managerRegistry
            ->getRepository(NodesSources::class)
            ->createQueryBuilder('ns')
            ->innerJoin('ns.node', 'n');
    }

    /**
     * Loop over every NodesSources to index them again.
     *
     * @param int $batchCount  split reindex span to several batches
     * @param int $batchNumber execute reindex on a specific batch
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function reindexAll(int $batchCount = 1, int $batchNumber = 0): void
    {
        $update = $this->getSolr()->createUpdate();
        /*
         * Use buffered insertion
         */
        /** @var BufferedAdd $buffer */
        $buffer = $this->getSolr()->getPlugin('bufferedadd');
        $buffer->setBufferSize(100);

        $countQuery = $this->getAllQueryBuilder()
            ->select('count(ns)')
            ->getQuery();
        $count = (int) $countQuery->getSingleScalarResult();

        $baseQb = $this->getAllQueryBuilder()->addSelect('n');
        if ($batchCount > 1) {
            $limit = (int) ceil($count / $batchCount);
            $offset = (int) $batchNumber * $limit;
            if ($batchNumber === $batchCount - 1) {
                $limit = $count - $offset;
                $baseQb->setMaxResults($limit)->setFirstResult($offset);
                if (null !== $this->io) {
                    $this->io->note(sprintf('Batch mode enabled (last): from %d to %d', $offset, ($offset + $limit) - 1));
                }
            } else {
                $baseQb->setMaxResults($limit)->setFirstResult($offset);
                if (null !== $this->io) {
                    $this->io->note(sprintf('Batch mode enabled: from %d to %d', $offset, ($offset + $limit) - 1));
                }
            }
            $count = $limit;
        }

        /*
         * Must use Paginator to avoid missing items due to SQL pagination issues with offset and limit
         */
        $paginator = new Paginator($baseQb->getQuery(), true);

        $this->io?->title(get_class($this));
        $this->io?->progressStart($count);

        foreach ($paginator as $row) {
            $solarium = $this->solariumFactory->createWithNodesSources($row);
            $solarium->createEmptyDocument($update);
            $solarium->index();
            $buffer->addDocument($solarium->getDocument());

            $this->io?->progressAdvance();
            // detach from Doctrine, so that it can be Garbage-Collected immediately
            $this->managerRegistry->getManager()->detach($row);
        }

        $buffer->flush();

        // optimize the index
        $this->optimizeSolr();

        $this->io?->progressFinish();
    }
}
