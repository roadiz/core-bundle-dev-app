<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use RZ\Roadiz\CoreBundle\Doctrine\SchemaUpdater;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

final class AppMigrateCommand extends Command
{
    protected string $projectDir;
    private SchemaUpdater $schemaUpdater;

    public function __construct(SchemaUpdater $schemaUpdater, string $projectDir, ?string $name = null)
    {
        parent::__construct($name);
        $this->projectDir = $projectDir;
        $this->schemaUpdater = $schemaUpdater;
    }

    protected function configure(): void
    {
        $this->setName('app:migrate')
            ->setDescription('Perform app:install and generate NS entities classes and Doctrine migrations.')
            ->addOption(
                'dry-run',
                'd',
                InputOption::VALUE_NONE,
                'Do nothing, only print information.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $question = new ConfirmationQuestion(
            '<question>Are you sure to migrate against app config.yml file?</question> This will generate new Doctrine Migrations and execute them. If you just want to import node-types run `bin/console app:install` instead',
            !$input->isInteractive()
        );
        if ($io->askQuestion($question) === false) {
            $io->note('Nothing was done…');
            return 0;
        }

        if ($input->getOption('dry-run')) {
            $this->runCommand(
                'app:install',
                '--dry-run',
                null,
                $input->isInteractive(),
                $output->isQuiet(),
            );
        } else {
            $this->runCommand(
                'app:install',
                '',
                null,
                $input->isInteractive(),
                $output->isQuiet()
            ) === 0 ? $io->success('app:install') : $io->error('app:install');

            $this->runCommand(
                'generate:nsentities',
                '',
                null,
                $input->isInteractive(),
                $output->isQuiet()
            ) === 0 ? $io->success('generate:nsentities') : $io->error('generate:nsentities');

            $this->schemaUpdater->updateNodeTypesSchema();
            $this->schemaUpdater->updateSchema();
            $io->success('doctrine-migrations');

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
        return 0;
    }

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
        $process->setWorkingDirectory($this->projectDir);
        $process->setTty($interactive);
        $process->run();
        return $process->wait();
    }
}
