<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use RZ\Roadiz\CoreBundle\Entity\Role;
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
    protected function configure(): void
    {
        $this->setName('users:create')
            ->setDescription('Create a user. Without <info>--password</info> a random password will be generated and sent by email. <info>Check if "email_sender" setting is valid.</info>')
            ->addOption('email', 'm', InputOption::VALUE_REQUIRED, 'Set user email.')
            ->addOption('password', 'p', InputOption::VALUE_REQUIRED, 'Set user password (typing plain password in command-line is insecure).')
            ->addOption('back-end', 'b', InputOption::VALUE_NONE, 'Add ROLE_BACKEND_USER to user.')
            ->addOption('super-admin', 's', InputOption::VALUE_NONE, 'Add ROLE_SUPERADMIN to user.')
            ->addUsage('--email=test@test.com --password=secret --back-end --super-admin test')
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

        if (!\is_string($name) || empty($name)) {
            throw new \InvalidArgumentException('Username argument is required.');
        }

        /** @var User|null $user */
        $user = $this->managerRegistry
            ->getRepository(User::class)
            ->findOneBy(['username' => $name]);

        if ($user instanceof User) {
            $io->warning('User “' . $name . '” already exists.');
            return 1;
        }

        $user = $this->executeUserCreation($name, $input, $output);

        // Change password right away
        $command = $this->getApplication()->find('users:password');
        $arguments = [
            'username' => $user->getUsername(),
        ];
        $passwordInput = new ArrayInput($arguments);
        return $command->run($passwordInput, $output);
    }

    /**
     * @param string          $username
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return User
     */
    private function executeUserCreation(
        string $username,
        InputInterface $input,
        OutputInterface $output
    ): User {
        $user = new User();
        $io = new SymfonyStyle($input, $output);
        if (!$input->hasOption('password')) {
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
                !filter_var($email, FILTER_VALIDATE_EMAIL) ||
                $this->managerRegistry->getRepository(User::class)->emailExists($email)
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
                $user->addRoleEntity($this->getRole(Role::ROLE_BACKEND_USER));
            }
        } elseif ($input->getOption('back-end') === true) {
            $user->addRoleEntity($this->getRole(Role::ROLE_BACKEND_USER));
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
                $user->addRoleEntity($this->getRole(Role::ROLE_SUPERADMIN));
            }
        } elseif ($input->getOption('super-admin') === true) {
            $user->addRoleEntity($this->getRole(Role::ROLE_SUPERADMIN));
        }

        if ($input->getOption('password')) {
            if (\mb_strlen($input->getOption('password')) < 5) {
                throw new \InvalidArgumentException('Password is too short.');
            }

            $user->setPlainPassword($input->getOption('password'));
        }

        $this->managerRegistry->getManagerForClass(User::class)->persist($user);
        $this->managerRegistry->getManagerForClass(User::class)->flush();

        $io->success('User “' . $username . '”<' . $email . '> created no password.');
        return $user;
    }
}
