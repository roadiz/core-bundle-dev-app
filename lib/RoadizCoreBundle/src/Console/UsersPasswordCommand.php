<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\Random\PasswordGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command line utils for managing users from terminal.
 */
final class UsersPasswordCommand extends UsersCommand
{
    private PasswordGenerator $passwordGenerator;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param PasswordGenerator $passwordGenerator
     */
    public function __construct(ManagerRegistry $managerRegistry, PasswordGenerator $passwordGenerator)
    {
        parent::__construct($managerRegistry);
        $this->passwordGenerator = $passwordGenerator;
    }

    protected function configure(): void
    {
        $this->setName('users:password')
            ->setDescription('Regenerate a new password for user')
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

        if ($name) {
            /** @var User|null $user */
            $user = $this->managerRegistry
                ->getRepository(User::class)
                ->findOneBy(['username' => $name]);

            if (null !== $user) {
                $confirmation = new ConfirmationQuestion(
                    '<question>Do you really want to regenerate user “' . $user->getUsername() . '” password?</question>',
                    false
                );
                if (
                    !$input->isInteractive() || $io->askQuestion(
                        $confirmation
                    )
                ) {
                    $user->setPlainPassword($this->passwordGenerator->generatePassword(12));
                    $this->managerRegistry->getManagerForClass(User::class)->flush();
                    $io->success('A new password was regenerated for ' . $name . ': ' . $user->getPlainPassword());
                } else {
                    $io->warning('User password was not changed.');
                }
            } else {
                throw new \InvalidArgumentException('User “' . $name . '” does not exist.');
            }
        }
        return 0;
    }
}
