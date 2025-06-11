<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use RZ\Roadiz\CoreBundle\Entity\User;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

final class UsersUnexpireCommand extends UsersCommand
{
    #[\Override]
    protected function configure(): void
    {
        $this->setName('users:unexpire')
            ->setDescription('Remove a user account expiration date')
            ->addArgument(
                'username',
                InputArgument::REQUIRED,
                'Username'
            );
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $name = $input->getArgument('username');
        $user = $this->getUserForInput($input);

        $confirmation = new ConfirmationQuestion(
            '<question>Do you really want to remove user “'.$user->getUsername().'” expiration date?</question>',
            false
        );
        if (
            !$input->isInteractive() || $io->askQuestion(
                $confirmation
            )
        ) {
            $user->setExpiresAt(null);
            $this->managerRegistry->getManagerForClass(User::class)->flush();
            $io->success('User “'.$name.'” unexpired.');

            return 0;
        } else {
            $io->warning('User “'.$name.'” was not updated.');

            return 1;
        }
    }
}
