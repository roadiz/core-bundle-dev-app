<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Doctrine;

use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Cache\Clearer\OPCacheClearer;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;
use Symfony\Component\Process\Process;

final readonly class SchemaUpdater
{
    public function __construct(
        private CacheClearerInterface $cacheClearer,
        private OPCacheClearer $opCacheClearer,
        private LoggerInterface $logger,
        private string $projectDir,
    ) {
    }

    public function clearMetadata(): void
    {
        $this->opCacheClearer->clear();
        $this->cacheClearer->clear('');

        $process = $this->runCommand(
            'doctrine:cache:clear-metadata',
        );
        $process->run();

        if (0 === $process->wait()) {
            $this->logger->info('Cleared Doctrine metadata cache.');
        } else {
            throw new \RuntimeException('Cannot clear Doctrine metadata cache. '.$process->getErrorOutput());
        }

        $process = $this->runCommand(
            'messenger:stop-workers',
        );
        $process->run();

        if (0 === $process->wait()) {
            $this->logger->info('Stop any running messenger worker to force them to restart');
        } else {
            throw new \RuntimeException('Cannot stop messenger workers. '.$process->getErrorOutput());
        }
    }

    public function clearAllCaches(): void
    {
        $this->opCacheClearer->clear();

        $process = $this->runCommand(
            'cache:clear',
        );
        $process->run();

        if (0 === $process->wait()) {
            $this->logger->info('Cleared all caches.');
        } else {
            throw new \RuntimeException('Cannot clear cache. '.$process->getErrorOutput());
        }
    }

    /**
     * Update database schema using doctrine migration.
     *
     * @throws \Exception
     */
    public function updateSchema(): void
    {
        $this->clearMetadata();

        $process = $this->runCommand(
            'doctrine:migrations:migrate',
        );
        $process->run();

        if (0 === $process->wait()) {
            $this->logger->info('Executed pending migrations.');
        } else {
            throw new \RuntimeException('Migrations failed. '.$process->getErrorOutput());
        }
    }

    /**
     * @throws \Exception
     */
    public function updateNodeTypesSchema(): void
    {
        $this->clearMetadata();
        $process = $this->runCommand(
            'doctrine:migrations:diff',
            '--namespace=DoctrineMigrations --quiet --allow-empty-diff',
        );
        $process->run();
        if (0 === $process->wait()) {
            $this->logger->info('New migration has been generated.');
            $this->updateSchema();
        } else {
            throw new \RuntimeException('DB schema update failed. '.$process->getErrorOutput());
        }
    }

    private function runCommand(
        string $command,
        string $args = '',
    ): Process {
        $args .= ' --no-interaction';
        $args .= ' --quiet';

        $process = Process::fromShellCommandline(
            'php bin/console '.$command.' '.$args
        );
        $process->setWorkingDirectory($this->projectDir);
        $process->setTty(false);

        return $process;
    }
}
