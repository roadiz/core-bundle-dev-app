<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\LoginAttempt;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @package RZ\Roadiz\CoreBundle\Console
 */
class CleanLoginAttemptCommand extends Command
{
    protected ManagerRegistry $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct();
        $this->managerRegistry = $managerRegistry;
    }

    protected function configure(): void
    {
        $this->setName('login-attempts:clean')
            ->setDescription('Clean all login attempts older than 1 day');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->managerRegistry->getRepository(LoginAttempt::class)->cleanLoginAttempts();

        $io->success('All login attempts older than 1 day were deleted.');

        return 0;
    }
}
