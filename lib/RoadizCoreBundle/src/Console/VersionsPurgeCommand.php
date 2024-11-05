<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\UserLogEntry;
use RZ\Roadiz\CoreBundle\Repository\UserLogEntryRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

final class VersionsPurgeCommand extends Command
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('versions:purge')
            ->setDescription('Purge entities versions')
            ->setHelp(<<<EOT
Purge entities versions <info>before</info> a given date-time
OR by keeping at least <info>count</info> versions.

This command does not alter active node-sources, document translations
or tag translations, it only deletes versioned log entries.
EOT
            )
            ->addOption(
                'before',
                'b',
                InputOption::VALUE_REQUIRED,
                'Purge versions older than <info>before</info> date <info>(any format accepted by \DateTime)</info>.'
            )
            ->addOption(
                'count',
                'c',
                InputOption::VALUE_REQUIRED,
                'Keeps only <info>count</info> versions for each entities (count must be greater than 1).'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->hasOption('before') && '' != $input->getOption('before')) {
            $this->purgeByDate($input, $output);
        } elseif ($input->hasOption('count')) {
            if ((int) $input->getOption('count') < 2) {
                throw new \InvalidArgumentException('Count option must be greater than 1.');
            }
            $this->purgeByCount($input, $output);
        } else {
            throw new \InvalidArgumentException('Choose an option between --before or --count');
        }

        return 0;
    }

    private function getRepository(): UserLogEntryRepository
    {
        return $this->managerRegistry->getRepository(UserLogEntry::class);
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    private function purgeByDate(InputInterface $input, OutputInterface $output): void
    {
        $io = new SymfonyStyle($input, $output);
        $dateTime = new \DateTime($input->getOption('before'));

        if ($dateTime >= new \DateTime()) {
            throw new \InvalidArgumentException('Before date must be in the past.');
        }
        $count = $this->getRepository()->countAllBeforeLoggedIn($dateTime);
        $question = new ConfirmationQuestion(sprintf(
            'Do you want to purge <info>%s</info> version(s) before <info>%s</info>?',
            $count,
            $dateTime->format('c')
        ), false);
        if (
            !$input->isInteractive() || $io->askQuestion(
                $question
            )
        ) {
            $result = $this->getRepository()->deleteAllBeforeLoggedIn($dateTime);
            $io->success(sprintf('%s version(s) were deleted.', $result));
        }
    }

    private function purgeByCount(InputInterface $input, OutputInterface $output): void
    {
        $io = new SymfonyStyle($input, $output);
        $count = (int) $input->getOption('count');

        $question = new ConfirmationQuestion(sprintf(
            'Do you want to purge all entities versions and to keep only the <info>latest %s</info>?',
            $count
        ), false);
        if (
            !$input->isInteractive() || $io->askQuestion(
                $question
            )
        ) {
            $deleteCount = $this->getRepository()->deleteAllExceptCount($count);
            $io->success(sprintf('%s version(s) were deleted.', $deleteCount));
        }
    }
}
