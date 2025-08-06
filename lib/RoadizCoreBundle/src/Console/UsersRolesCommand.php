<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\User;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command line utils for managing users from terminal.
 */
final class UsersRolesCommand extends UsersCommand
{
    public function __construct(
        ManagerRegistry $managerRegistry,
        ?string $name = null,
    ) {
        parent::__construct($managerRegistry, $name);
    }

    #[\Override]
    protected function configure(): void
    {
        $this->setName('users:roles')
            ->setDescription('Manage user roles')
            ->addArgument(
                'username',
                InputArgument::REQUIRED,
                'Username'
            )
            ->addOption(
                'add',
                'a',
                InputOption::VALUE_NONE,
                'Add roles to a user'
            )
            ->addOption(
                'remove',
                'r',
                InputOption::VALUE_NONE,
                'Remove roles from a user'
            );
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $user = $this->getUserForInput($input);

        if ($input->getOption('add')) {
            $question = new Question(
                'Enter the role name to add'
            );

            do {
                $role = $io->askQuestion($question);
                if ('' != $role) {
                    $user->setUserRoles([
                        ...$user->getUserRoles(),
                        $role,
                    ]);
                    $this->managerRegistry->getManagerForClass(User::class)->flush();
                    $io->success('Role: '.$role.' added.');
                }
            } while ('' != $role);
        } elseif ($input->getOption('remove')) {
            do {
                $roles = $user->getUserRoles();
                $question = new Question(
                    'Enter the role name to remove'
                );
                $question->setAutocompleterValues($roles);

                $role = $io->askQuestion($question);
                if (in_array($role, $roles)) {
                    $user->setUserRoles(
                        array_values(array_filter($roles, fn ($r) => $r !== $role))
                    );
                    $this->managerRegistry->getManagerForClass(User::class)->flush();
                    $io->success('Role: '.$role.' removed.');
                }
            } while ('' != $role);
        }

        return 0;
    }
}
