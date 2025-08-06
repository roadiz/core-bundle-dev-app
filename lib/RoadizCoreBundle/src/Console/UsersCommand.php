<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UsersCommand extends Command
{
    public function __construct(
        protected readonly ManagerRegistry $managerRegistry,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    #[\Override]
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

    protected function getUserTableRow(User $user): array
    {
        return [
            'Id' => $user->getId(),
            'Username' => $user->getUsername(),
            'Email' => $user->getEmail(),
            'Disabled' => (!$user->isEnabled() ? 'X' : ''),
            'Expired' => (!$user->isAccountNonExpired() ? 'X' : ''),
            'Locked' => (!$user->isAccountNonLocked() ? 'X' : ''),
            'Groups' => implode(' ', $user->getGroupNames()),
        ];
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $name = $input->getArgument('username');

        if ($name) {
            /** @var User|null $user */
            $user = $this->managerRegistry
                ->getRepository(User::class)
                ->findOneBy(['username' => $name]);

            if (null === $user) {
                $io->error('User “'.$name.'” does not exist… use users:create to add a new user.');
            } else {
                $tableContent = [
                    $this->getUserTableRow($user),
                ];
                $io->table(
                    array_keys($tableContent[0]),
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
                    $tableContent[] = $this->getUserTableRow($user);
                }

                $io->table(
                    array_keys($tableContent[0]),
                    $tableContent
                );
            } else {
                $io->warning('No available users.');
            }
        }

        return 0;
    }

    protected function getUserForInput(InputInterface $input): User
    {
        $name = $input->getArgument('username');

        if (!\is_string($name) || empty($name)) {
            throw new InvalidArgumentException('Username argument is required.');
        }

        /** @var User|null $user */
        $user = $this->managerRegistry
            ->getRepository(User::class)
            ->findOneBy(['username' => $name]);

        if (!($user instanceof User)) {
            throw new InvalidArgumentException('User “'.$name.'” does not exist.');
        }

        return $user;
    }
}
