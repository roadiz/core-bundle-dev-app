<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\Console;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\UserBundle\Entity\UserValidationToken;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class PurgeUserValidationTokenCommand extends Command
{
    public function __construct(private readonly ManagerRegistry $managerRegistry, ?string $name = null)
    {
        parent::__construct($name);
    }

    #[\Override]
    protected function configure(): void
    {
        $this
            ->setName('users:purge-validation-tokens')
            ->setDescription('Purge expired user validation tokens.');
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $count = $this->managerRegistry->getRepository(UserValidationToken::class)->deleteAllExpired();

        $io->success(sprintf('%d expired user validation token(s) were deleted.', $count));

        return 0;
    }
}
