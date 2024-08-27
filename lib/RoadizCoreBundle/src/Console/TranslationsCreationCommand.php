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
 * Command line utils for managing translations
 */
final class TranslationsCreationCommand extends TranslationsCommand
{
    protected function configure(): void
    {
        $this->setName('translations:create')
            ->setDescription('Create a translation')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'Translation name'
            )
            ->addArgument(
                'locale',
                InputArgument::REQUIRED,
                'Translation locale'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $name = $input->getArgument('name');
        $locale = $input->getArgument('locale');

        if (!$name) {
            return 1;
        }

        $translationByName = $this->managerRegistry
            ->getRepository(Translation::class)
            ->findOneByName($name);
        $translationByLocale = $this->managerRegistry
            ->getRepository(Translation::class)
            ->findOneByLocale($locale);

        $confirmation = new ConfirmationQuestion(
            '<question>Are you sure to create ' . $name . ' (' . $locale . ') translation?</question>',
            false
        );

        if (null !== $translationByName) {
            $io->error('Translation ' . $name . ' already exists.');
            return 1;
        } elseif (null !== $translationByLocale) {
            $io->error('Translation locale ' . $locale . ' is already used.');
            return 1;
        }

        if (
            $io->askQuestion(
                $confirmation
            )
        ) {
            $newTrans = new Translation();
            $newTrans->setName($name)
                ->setLocale($locale);

            $this->managerRegistry->getManagerForClass(Translation::class)->persist($newTrans);
            $this->managerRegistry->getManagerForClass(Translation::class)->flush();

            $io->success('New ' . $newTrans->getName() . ' translation for ' . $newTrans->getLocale() . ' locale.');
        }
        return 0;
    }
}
