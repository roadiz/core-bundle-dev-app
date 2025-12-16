<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use RZ\Roadiz\CoreBundle\Entity\User;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

final class UsersCreationCommand extends UsersCommand
{
    #[\Override]
    protected function configure(): void
    {
        $this->setName('users:create')
            ->setDescription('Create a user. Without <info>--password</info> a random password will be generated and sent by email.')
            ->addOption('email', 'm', InputOption::VALUE_REQUIRED, 'Set user email.')
            ->addOption('plain-password', 'p', InputOption::VALUE_REQUIRED, 'Set user password (typing plain password in command-line is insecure).')
            ->addOption('back-end', 'b', InputOption::VALUE_NONE, 'Add ROLE_BACKEND_USER to user.')
            ->addOption('super-admin', 's', InputOption::VALUE_NONE, 'Add ROLE_SUPERADMIN to user.')
            ->addUsage('--email=test@test.com --password=secret --back-end --super-admin test')
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

        if (!\is_string($name) || empty($name)) {
            throw new \InvalidArgumentException('Username argument is required.');
        }

        if (is_string($input->getOption('plain-password')) && \mb_strlen($input->getOption('plain-password')) < 12) {
            throw new \InvalidArgumentException('Password should be at least 12 chars long.');
        }

        /** @var User|null $user */
        $user = $this->managerRegistry
            ->getRepository(User::class)
            ->findOneBy(['username' => $name]);

        if ($user instanceof User) {
            $io->warning('User “'.$name.'” already exists.');

            return self::FAILURE;
        }

        $user = $this->executeUserCreation($name, $input, $output);

        // Change password right away
        $command = $this->getApplication()?->find('users:password');
        if (null === $command) {
            return self::SUCCESS;
        }
        $arguments = [
            'username' => $user->getUsername(),
        ];
        $passwordInput = new ArrayInput($arguments);

        if ($plainPassword = $input->getOption('plain-password')) {
            $passwordInput = new ArrayInput([
                'username' => $user->getUsername(),
                '--plain-password' => $plainPassword,
            ]);
            $passwordInput->setInteractive(false);
        }

        return $command->run($passwordInput, $output);
    }

    private function executeUserCreation(
        string $username,
        InputInterface $input,
        OutputInterface $output,
    ): User {
        $user = new User();
        $io = new SymfonyStyle($input, $output);
        if (!$input->hasOption('plain-password')) {
            $user->sendCreationConfirmationEmail(true);
        }
        $user->setUsername($username);

        if ($input->isInteractive() && !$input->getOption('email')) {
            /*
             * Interactive
             */
            do {
                $questionEmail = new Question(
                    '<question>Email</question>'
                );
                $email = $io->askQuestion(
                    $questionEmail
                );
            } while (
                !filter_var($email, FILTER_VALIDATE_EMAIL)
                || $this->managerRegistry->getRepository(User::class)->emailExists($email)
            );
        } else {
            /*
             * From CLI
             */
            $email = $input->getOption('email');
            if ($this->managerRegistry->getRepository(User::class)->emailExists($email)) {
                throw new \InvalidArgumentException('Email already exists.');
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new \InvalidArgumentException('Email is not valid.');
            }
        }

        $user->setEmail($email);

        if ($input->isInteractive() && !$input->getOption('back-end')) {
            $questionBack = new ConfirmationQuestion(
                '<question>Is user a backend user?</question>',
                false
            );
            if (
                $io->askQuestion(
                    $questionBack
                )
            ) {
                $user->setUserRoles([
                    ...$user->getUserRoles(),
                    'ROLE_BACKEND_USER',
                ]);
            }
        } elseif (true === $input->getOption('back-end')) {
            $user->setUserRoles([
                ...$user->getUserRoles(),
                'ROLE_BACKEND_USER',
            ]);
        }

        if ($input->isInteractive() && !$input->getOption('super-admin')) {
            $questionAdmin = new ConfirmationQuestion(
                '<question>Is user a super-admin user?</question>',
                false
            );
            if (
                $io->askQuestion(
                    $questionAdmin
                )
            ) {
                $user->setUserRoles([
                    ...$user->getUserRoles(),
                    'ROLE_SUPERADMIN',
                ]);
            }
        } elseif (true === $input->getOption('super-admin')) {
            $user->setUserRoles([
                ...$user->getUserRoles(),
                'ROLE_SUPERADMIN',
            ]);
        }

        $this->managerRegistry->getManagerForClass(User::class)?->persist($user);
        $this->managerRegistry->getManagerForClass(User::class)?->flush();

        $io->success('User “'.$username.'”<'.$email.'> created no password.');

        return $user;
    }
}
