<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command line utils for managing translations.
 */
class TranslationsDisableCommand extends Command
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
        $this->setName('translations:disable')
            ->setDescription('Disables a translation')
            ->addArgument(
                'locale',
                InputArgument::REQUIRED,
                'Translation locale'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $locale = $input->getArgument('locale');

        $translation = $this->managerRegistry
            ->getRepository(Translation::class)
            ->findOneByLocale($locale);

        if ($translation !== null) {
            $confirmation = new ConfirmationQuestion(
                '<question>Are you sure to disable ' . $translation->getName() . ' (' . $translation->getLocale() . ') translation?</question>',
                false
            );
            if (
                $io->askQuestion(
                    $confirmation
                )
            ) {
                $translation->setAvailable(false);
                $this->managerRegistry->getManagerForClass(Translation::class)->flush();
                $io->success('Translation disabled.');
            }
        } else {
            $io->error('Translation for locale ' . $locale . ' does not exist.');
            return 1;
        }
        return 0;
    }
}
