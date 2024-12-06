<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class UsersInactiveCommand extends Command
{
    public function __construct(private readonly ManagerRegistry $managerRegistry, ?string $name = null)
    {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setName('users:inactive')
            ->setDescription('List users that did not logged-in for <info>30</info> days.')
            ->addOption(
                'days',
                'd',
                InputOption::VALUE_REQUIRED,
                'Number of days since last login.',
                30
            )
            ->addOption(
                'role',
                'r',
                InputOption::VALUE_REQUIRED,
                'List users <info>with</info> a specific role.',
                null
            )
            ->addOption(
                'missing-role',
                'm',
                InputOption::VALUE_REQUIRED,
                'List users <info>without</info> a specific role. Can be combined with --role.',
                null
            )
            ->addOption(
                'purge',
                null,
                InputOption::VALUE_NONE,
                'Purge and delete inactive users <info>with a specific role</info>, <error>destructive action</error>.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $daysCount = $input->getOption('days');
        if (!\is_numeric($daysCount) || $daysCount < 1) {
            $io->error('Days option must be a positive number.');

            return 1;
        }

        $sinceDate = new \DateTimeImmutable("-$daysCount days");

        $inactiveUsers = $this->managerRegistry
            ->getRepository(User::class)
            ->findAllInactiveSinceDays(
                (int) $daysCount
            )
        ;

        $filteringRole = $input->getOption('role');
        if (\is_string($filteringRole) && !empty(trim($filteringRole))) {
            $inactiveUsers = array_filter($inactiveUsers, function (User $user) use ($filteringRole) {
                return \in_array($filteringRole, $user->getRoles(), true);
            });
        }

        $missingRole = $input->getOption('missing-role');
        if (\is_string($missingRole) && !empty(trim($missingRole))) {
            $inactiveUsers = array_filter($inactiveUsers, function (User $user) use ($missingRole) {
                return !\in_array($missingRole, $user->getRoles(), true);
            });
        }

        $io->success(sprintf(
            '%d inactive users since %s.',
            count($inactiveUsers),
            $sinceDate->format('Y-m-d')
        ));

        if ($output->isVerbose() && count($inactiveUsers) > 0) {
            $io->table(
                ['ID', 'Username', 'Last login', 'Created at'],
                array_map(function (User $user) {
                    return [
                        $user->getId(),
                        $user->getUsername(),
                        $user->getLastLogin()?->format('Y-m-d H:i:s') ?? 'Never',
                        $user->getCreatedAt()?->format('Y-m-d H:i:s') ?? 'Never',
                    ];
                }, $inactiveUsers)
            );
        }

        $purge = $input->getOption('purge');
        if (!$purge || 0 === count($inactiveUsers)) {
            return 0;
        }

        if (!\is_string($filteringRole) || empty(trim($filteringRole))) {
            $io->error(sprintf(
                'You cannot purge inactive users since %s without filtering them by a ROLE name.',
                $sinceDate->format('Y-m-d')
            ));

            return 1;
        }

        if ($input->isInteractive() && !$io->confirm('Do you want to delete these users?')) {
            $io->comment('No user has been deleted.');

            return 0;
        }

        foreach ($inactiveUsers as $user) {
            $this->managerRegistry->getManager()->remove($user);
        }
        $this->managerRegistry->getManager()->flush();
        $io->success(sprintf(
            '%d inactive users have been deleted.',
            count($inactiveUsers)
        ));

        return 0;
    }
}
