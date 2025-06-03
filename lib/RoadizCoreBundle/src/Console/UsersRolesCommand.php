<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Bag\Roles;
use RZ\Roadiz\CoreBundle\Entity\Role;
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
        private readonly Roles $rolesBag,
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
            $roles = $this->managerRegistry
                ->getRepository(Role::class)
                ->getAllRoleName();

            $question = new Question(
                'Enter the role name to add'
            );
            $question->setAutocompleterValues($roles);

            do {
                $role = $io->askQuestion($question);
                if ('' != $role) {
                    $user->addRoleEntity($this->rolesBag->get($role));
                    $this->managerRegistry->getManagerForClass(User::class)->flush();
                    $io->success('Role: '.$role.' added.');
                }
            } while ('' != $role);
        } elseif ($input->getOption('remove')) {
            do {
                $roles = $user->getRoles();
                $question = new Question(
                    'Enter the role name to remove'
                );
                $question->setAutocompleterValues($roles);

                $role = $io->askQuestion($question);
                if (in_array($role, $roles)) {
                    $user->removeRoleEntity($this->rolesBag->get($role));
                    $this->managerRegistry->getManagerForClass(User::class)->flush();
                    $io->success('Role: '.$role.' removed.');
                }
            } while ('' != $role);
        }

        return 0;
    }
}
