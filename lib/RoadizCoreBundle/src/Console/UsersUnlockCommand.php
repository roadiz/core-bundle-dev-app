<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use RZ\Roadiz\CoreBundle\Entity\User;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

final class UsersUnlockCommand extends UsersCommand
{
    protected function configure(): void
    {
        $this->setName('users:unlock')
            ->setDescription('Unlock a user account')
            ->addArgument(
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

        $confirmation = new ConfirmationQuestion(
            '<question>Do you really want to unlock user “'.$user->getUsername().'”?</question>',
            false
        );
        if (
            !$input->isInteractive() || $io->askQuestion(
                $confirmation
            )
        ) {
            $user->setLocked(false);
            $this->managerRegistry->getManagerForClass(User::class)->flush();
            $io->success('User “'.$name.'” unlocked.');

            return 0;
        } else {
            $io->warning('User “'.$name.'” was not unlocked.');

            return 1;
        }
    }
}
