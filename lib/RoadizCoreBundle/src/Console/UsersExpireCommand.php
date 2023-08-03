<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use RZ\Roadiz\CoreBundle\Entity\User;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

final class UsersExpireCommand extends UsersCommand
{
    protected function configure(): void
    {
        $this->setName('users:expire')
            ->setDescription('Set a user account expiration date')
            ->addArgument(
                'username',
                InputArgument::REQUIRED,
                'Username'
            )
            ->addArgument(
                'expiry',
                InputArgument::OPTIONAL,
                'Expiration date and time (Y-m-d H:i:s)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $name = $input->getArgument('username');
        $user = $this->getUserForInput($input);
        $expirationDate = new \DateTime($input->getArgument('expiry') ?? 'now');

        $question = sprintf(
            '<question>Do you really want to set user “%s” expiration date on %s?</question>',
            $user->getUsername(),
            $expirationDate->format('c')
        );
        $confirmation = new ConfirmationQuestion($question, false);
        if (
            !$input->isInteractive() || $io->askQuestion(
                $confirmation
            )
        ) {
            $user->setExpiresAt($expirationDate);
            $this->managerRegistry->getManagerForClass(User::class)->flush();
            $io->success('User “' . $name . '” expiration date was set on ' . $expirationDate->format('c') . '.');
            return 0;
        } else {
            $io->warning('User “' . $name . '” was not updated.');
            return 1;
        }
    }
}
