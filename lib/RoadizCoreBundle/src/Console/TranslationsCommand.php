<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command line utils for managing translations from terminal.
 */
class TranslationsCommand extends Command
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
        $this->setName('translations:list')
            ->setDescription('List translations');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $translations = $this->managerRegistry
            ->getRepository(Translation::class)
            ->findAll();

        if (count($translations) > 0) {
            $tableContent = [];
            /** @var Translation $trans */
            foreach ($translations as $trans) {
                $tableContent[] = [
                    $trans->getId(),
                    $trans->getName(),
                    $trans->getLocale(),
                    (!$trans->isAvailable() ? 'X' : ''),
                    ($trans->isDefaultTranslation() ? 'X' : ''),
                ];
            }
            $io->table(['Id', 'Name', 'Locale', 'Disabled', 'Default'], $tableContent);
        } else {
            $io->error('No available translations.');
        }
        return 0;
    }
}
