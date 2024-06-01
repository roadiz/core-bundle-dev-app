<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

trait RunningCommandsTrait
{
    abstract protected function getProjectDir(): string;

    protected function runCommand(
        string $command,
        string $args = '',
        ?string $environment = null,
        bool $interactive = true,
        bool $quiet = false
    ): int {
        $args .= $interactive ? '' : ' --no-interaction ';
        $args .= $quiet ? ' --quiet ' : ' -v ';
        $args .= is_string($environment) ? (' --env ' . $environment) : '';

        $process = Process::fromShellCommandline(
            'php bin/console ' . $command  . ' ' . $args
        );
        $process->setWorkingDirectory($this->getProjectDir());
        $process->setTty($interactive);
        $process->run();
        return $process->wait();
    }

    protected function clearCaches(SymfonyStyle $io): void
    {
        $this->runCommand(
            'doctrine:cache:clear-metadata',
            '',
            null,
            false,
            true
        ) === 0 ? $io->success('doctrine:cache:clear-metadata') : $io->error('doctrine:cache:clear-metadata');

        $this->runCommand(
            'cache:clear',
            '',
            null,
            false,
            true
        ) === 0 ? $io->success('cache:clear') : $io->error('cache:clear');

        $this->runCommand(
            'cache:pool:clear',
            'cache.global_clearer',
            null,
            false,
            true
        ) === 0 ? $io->success('cache:pool:clear') : $io->error('cache:pool:clear');
    }
}
