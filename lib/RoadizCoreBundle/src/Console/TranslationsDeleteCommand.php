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
class TranslationsDeleteCommand extends Command
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
        $this->setName('translations:delete')
            ->setDescription('Delete a translation')
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
        $translationCount = $this->managerRegistry
            ->getRepository(Translation::class)
            ->countBy([]);

        if ($translationCount < 2) {
            $io->error('You cannot delete the only one available translation!');
            return 1;
        } elseif ($translation !== null) {
            $io->note('///////////////////////////////' . PHP_EOL .
                '/////////// WARNING ///////////' . PHP_EOL .
                '///////////////////////////////' . PHP_EOL .
                'This operation cannot be undone.' . PHP_EOL .
                'Deleting a translation, you will automatically delete every translated tags, node-sources, url-aliases and documents.');
            $confirmation = new ConfirmationQuestion(
                '<question>Are you sure to delete ' . $translation->getName() . ' (' . $translation->getLocale() . ') translation?</question>',
                false
            );
            if (
                $io->askQuestion(
                    $confirmation
                )
            ) {
                $this->managerRegistry->getManagerForClass(Translation::class)->remove($translation);
                $this->managerRegistry->getManagerForClass(Translation::class)->flush();
                $io->success('Translation deleted.');
            }
        } else {
            $io->error('Translation for locale ' . $locale . ' does not exist.');
            return 1;
        }
        return 0;
    }
}
