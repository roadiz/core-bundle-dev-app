<?php

declare(strict_types=1);

namespace RZ\Roadiz\TwoFactorBundle\Console;

use RZ\Roadiz\TwoFactorBundle\Entity\TwoFactorUser;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'users:2fa:disable',
    description: 'Disable two-factor authentication for a user.',
)]
final class DisableTwoFactorUserCommand extends UsersCommand
{
    protected function configure(): void
    {
        $this->addArgument(
            'username',
            InputArgument::REQUIRED,
            'Username'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $name = $input->getArgument('username');
        $user = $this->getUserForInput($input);

        $twoFactorUser = $this->twoFactorUserProvider->getFromUser($user);

        if (!$twoFactorUser instanceof TwoFactorUser) {
            $io->warning('User “' . $name . '” does not have two-factor authentication enabled.');
            return 1;
        }

        $this->twoFactorUserProvider->disable($twoFactorUser);
        $io->success('Two-factor authentication disabled for user “' . $name . '”.');

        return 0;
    }
}
