<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use RZ\Roadiz\CoreBundle\Doctrine\SchemaUpdater;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class AppMigrateCommand extends Command
{
    use RunningCommandsTrait;

    public function __construct(
        private readonly SchemaUpdater $schemaUpdater,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    #[\Override]
    public function getProjectDir(): string
    {
        return $this->projectDir;
    }

    #[\Override]
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

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $question = new ConfirmationQuestion(
            '<question>Are you sure to migrate against app config.yml file?</question> This will generate new Doctrine Migrations and execute them. If you just want to import node-types run `bin/console app:install` instead',
            !$input->isInteractive()
        );
        if (false === $io->askQuestion($question)) {
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
            0 === $this->runCommand(
                'app:install',
                '',
                null,
                $input->isInteractive(),
                $output->isQuiet()
            ) ? $io->success('app:install') : $io->error('app:install');

            0 === $this->runCommand(
                'generate:nsentities',
                '',
                null,
                $input->isInteractive(),
                $output->isQuiet()
            ) ? $io->success('generate:nsentities') : $io->error('generate:nsentities');

            0 === $this->runCommand(
                'generate:api-resources',
                '',
                null,
                $input->isInteractive(),
                $output->isQuiet()
            ) ? $io->success('generate:api-resources') : $io->error('generate:api-resources');

            $this->schemaUpdater->updateNodeTypesSchema();
            $this->schemaUpdater->updateSchema();
            $io->success('doctrine-migrations');

            $this->clearCaches($io);
        }

        return 0;
    }
}
