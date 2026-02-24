<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\Random\PasswordGeneratorInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

final class UsersPasswordCommand extends UsersCommand
{
    public function __construct(
        private readonly PasswordGeneratorInterface $passwordGenerator,
        ManagerRegistry $managerRegistry,
        ?string $name = null,
    ) {
        parent::__construct($managerRegistry, $name);
    }

    protected function configure(): void
    {
        $this->setName('users:password')
            ->setDescription('Regenerate a new password for user')
            ->addArgument(
                'username',
                InputArgument::REQUIRED,
                'Username'
            )->addOption(
                'length',
                'l',
                InputOption::VALUE_OPTIONAL,
                default: 16,
            )
            ->addOption(
                'plain-password',
                'p',
                InputOption::VALUE_REQUIRED,
                'Set user password (typing plain password in command-line is insecure).'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $name = $input->getArgument('username');
        $user = $this->getUserForInput($input);
        $password = $this->passwordGenerator->generatePassword((int) $input->getOption('length'));

        $confirmation = new ConfirmationQuestion(
            '<question>Do you really want to regenerate user “'.$user->getUsername().'” password?</question>',
            false
        );
        if (
            !$input->isInteractive() || $io->askQuestion(
                $confirmation
            )
        ) {
            if ($plainPassword = $input->getOption('plain-password')) {
                if (\mb_strlen($plainPassword) < 12) {
                    throw new \InvalidArgumentException('Password should be at least 12 chars long.');
                }
                $password = $plainPassword;
            }
            $user->setPlainPassword($password);
            $this->managerRegistry->getManagerForClass(User::class)->flush();
            $io->success('A new password was regenerated for '.$name.': '.$user->getPlainPassword());

            return 0;
        }
        $io->warning('User password was not changed.');

        return 1;
    }
}
