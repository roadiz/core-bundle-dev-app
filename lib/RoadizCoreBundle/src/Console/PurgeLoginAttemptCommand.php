<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\LoginAttempt;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class PurgeLoginAttemptCommand
 *
 * @package RZ\Roadiz\CoreBundle\Console
 */
class PurgeLoginAttemptCommand extends Command
{
    protected ManagerRegistry $managerRegistry;

    /**
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct();
        $this->managerRegistry = $managerRegistry;
    }

    protected function configure(): void
    {
        $this->setName('login-attempts:purge')
            ->setDescription('Purge all login attempts for one IP address')
            ->addArgument(
                'ip-address',
                InputArgument::REQUIRED
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $this->managerRegistry
            ->getRepository(LoginAttempt::class)
            ->purgeLoginAttempts($input->getArgument('ip-address'));

        $io->success('All login attempts were deleted for ' . $input->getArgument('ip-address'));

        return 0;
    }
}
