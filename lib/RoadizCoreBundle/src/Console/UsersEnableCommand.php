<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use RZ\Roadiz\CoreBundle\Entity\User;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

final class UsersEnableCommand extends UsersCommand
{
    #[\Override]
    protected function configure(): void
    {
        $this->setName('users:enable')
            ->setDescription('Enable a user')
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
            '<question>Do you really want to enable user “'.$user->getUsername().'”?</question>',
            false
        );
        if (
            !$input->isInteractive() || $io->askQuestion(
                $confirmation
            )
        ) {
            $user->setEnabled(true);
            $this->managerRegistry->getManagerForClass(User::class)->flush();
            $io->success('User “'.$name.'” was enabled.');

            return 0;
        } else {
            $io->warning('User “'.$name.'” was not enabled');

            return 1;
        }
    }
}
