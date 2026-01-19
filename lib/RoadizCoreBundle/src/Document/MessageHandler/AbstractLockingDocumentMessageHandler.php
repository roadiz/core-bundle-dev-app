<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Document\MessageHandler;

use Doctrine\Persistence\ManagerRegistry;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Document\Message\AbstractDocumentMessage;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\SharedLockInterface;

/**
 * Use file locking system to ensure only one async operation is done on each document file.
 */
abstract class AbstractLockingDocumentMessageHandler extends AbstractDocumentMessageHandler
{
    public function __construct(
        protected readonly LockFactory $lockFactory,
        ManagerRegistry $managerRegistry,
        LoggerInterface $messengerLogger,
        FilesystemOperator $documentsStorage,
    ) {
        parent::__construct($managerRegistry, $messengerLogger, $documentsStorage);
    }

    protected function getLockKey(string $monthPath): string
    {
        return sha1($monthPath);
    }

    protected function getLockTtl(): int
    {
        return 15;
    }

    protected function isLockExclusive(): bool
    {
        return false;
    }

    /*
     * Block until lock is acquired.
     */
    protected function lock(string $monthPath): SharedLockInterface
    {
        $lock = $this->lockFactory->createLock($this->getLockKey($monthPath), ttl: $this->getLockTtl());

        if ($this->isLockExclusive()) {
            // Acquire an exclusive lock
            $lock->acquire(true);
        } else {
            // Default to a shared lock
            $lock->acquireRead(true);
        }

        return $lock;
    }

    #[\Override]
    public function __invoke(AbstractDocumentMessage $message): void
    {
        $document = $this->managerRegistry
            ->getRepository(DocumentInterface::class)
            ->find($message->getDocumentId());

        if ($document instanceof DocumentInterface && $this->supports($document)) {
            $documentPath = $document->getMountPath() ?? (string) $document;

            $lock = $this->lock($documentPath);
            $this->processMessage($message, $document);
            $this->managerRegistry->getManager()->flush();
            $lock->release();
        }
    }

    /**
     * Check if document file is stored locally.
     *
     * @phpstan-assert-if-true non-empty-string $document->getMountPath()
     */
    protected function isFileLocal(DocumentInterface $document): bool
    {
        return
            null !== $document->getMountPath()
            && (
                $document->isPrivate()
                || str_starts_with($this->documentsStorage->publicUrl($document->getMountPath()), '/')
            );
    }
}
