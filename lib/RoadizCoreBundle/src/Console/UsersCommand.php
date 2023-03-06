<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Role;
use RZ\Roadiz\CoreBundle\Entity\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command line utils for managing users from terminal.
 */
class UsersCommand extends Command
{
    protected ManagerRegistry $managerRegistry;

    /**
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct();
        $this->managerRegistry = $managerRegistry;
    }

    protected function configure(): void
    {
        $this->setName('users:list')
            ->setDescription('List all users or just one')
            ->addArgument(
                'username',
                InputArgument::OPTIONAL,
                'User name'
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

            if ($user === null) {
                $io->error('User “' . $name . '” does not exist… use users:create to add a new user.');
            } else {
                $tableContent = [[
                    $user->getId(),
                    $user->getUsername(),
                    $user->getEmail(),
                    (!$user->isEnabled() ? 'X' : ''),
                    ($user->getExpired() ? 'X' : ''),
                    (!$user->isAccountNonLocked() ? 'X' : ''),
                    implode(' ', $user->getGroupNames()),
                    implode(' ', $user->getRoles()),
                ]];
                $io->table(
                    ['Id', 'Username', 'Email', 'Disabled', 'Expired', 'Locked', 'Groups', 'Roles'],
                    $tableContent
                );
            }
        } else {
            $users = $this->managerRegistry
                ->getRepository(User::class)
                ->findAll();

            if (count($users) > 0) {
                $tableContent = [];
                foreach ($users as $user) {
                    $tableContent[] = [
                        $user->getId(),
                        $user->getUsername(),
                        $user->getEmail(),
                        (!$user->isEnabled() ? 'X' : ''),
                        ($user->getExpired() ? 'X' : ''),
                        (!$user->isAccountNonLocked() ? 'X' : ''),
                        implode(' ', $user->getGroupNames()),
                        implode(' ', $user->getRoles()),
                    ];
                }

                $io->table(
                    ['Id', 'Username', 'Email', 'Disabled', 'Expired', 'Locked', 'Groups', 'Roles'],
                    $tableContent
                );
            } else {
                $io->warning('No available users.');
            }
        }
        return 0;
    }

    /**
     * Get role by name, and create it if it does not exist.
     *
     * @param string $roleName
     *
     * @return Role
     */
    public function getRole(string $roleName = Role::ROLE_SUPERADMIN)
    {
        $role = $this->managerRegistry
            ->getRepository(Role::class)
            ->findOneBy(['name' => $roleName]);

        if ($role === null) {
            $role = new Role($roleName);
            $this->managerRegistry->getManagerForClass(Role::class)->persist($role);
            $this->managerRegistry->getManagerForClass(Role::class)->flush();
        }

        return $role;
    }
}
