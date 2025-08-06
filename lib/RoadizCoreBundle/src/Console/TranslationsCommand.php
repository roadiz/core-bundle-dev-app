<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'translations:list',
    description: 'List Roadiz translations.',
)]
class TranslationsCommand extends Command
{
    public function __construct(
        protected readonly ManagerRegistry $managerRegistry,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $translations = $this->managerRegistry
            ->getRepository(Translation::class)
            ->findAll();

        if (0 === count($translations)) {
            $io->error('No available translations.');

            return 1;
        }

        $tableContent = [];
        /** @var Translation $trans */
        foreach ($translations as $trans) {
            $tableContent[] = [
                $trans->getId(),
                $trans->getName(),
                $trans->getLocale(),
                !$trans->isAvailable() ? 'X' : '',
                $trans->isDefaultTranslation() ? 'X' : '',
            ];
        }
        $io->table(['Id', 'Name', 'Locale', 'Disabled', 'Default'], $tableContent);

        return 0;
    }
}
