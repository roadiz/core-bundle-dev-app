<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use RZ\Roadiz\CoreBundle\Entity\Translation;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command line utils for managing translations.
 */
final class TranslationsDisableCommand extends TranslationsCommand
{
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

        if (null === $translation) {
            $io->error('Translation for locale '.$locale.' does not exist.');

            return 1;
        }

        $confirmation = new ConfirmationQuestion(
            '<question>Are you sure to disable '.$translation->getName().' ('.$translation->getLocale().') translation?</question>',
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

        return 0;
    }
}
