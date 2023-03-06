<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Doctrine\Common\Cache\CacheProvider;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Importer\GroupsImporter;
use RZ\Roadiz\CoreBundle\Importer\RolesImporter;
use RZ\Roadiz\CoreBundle\Importer\SettingsImporter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;

/**
 * Command line utils for installing RZ-CMS v3 from terminal.
 */
class InstallCommand extends Command
{
    protected ManagerRegistry $managerRegistry;
    protected RolesImporter $rolesImporter;
    protected GroupsImporter $groupsImporter;
    protected SettingsImporter $settingsImporter;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param RolesImporter $rolesImporter
     * @param GroupsImporter $groupsImporter
     * @param SettingsImporter $settingsImporter
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        RolesImporter $rolesImporter,
        GroupsImporter $groupsImporter,
        SettingsImporter $settingsImporter
    ) {
        parent::__construct();
        $this->managerRegistry = $managerRegistry;
        $this->rolesImporter = $rolesImporter;
        $this->groupsImporter = $groupsImporter;
        $this->settingsImporter = $settingsImporter;
    }

    protected function configure(): void
    {
        $this
            ->setName('install')
            ->setDescription('Install Roadiz roles, settings, translations and default backend theme');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->note('Before installing Roadiz, did you create database schema? ' . PHP_EOL .
            'If not execute: bin/console doctrine:migrations:migrate');
        $question = new ConfirmationQuestion(
            '<question>Are you sure to perform installation?</question>',
            false
        );

        if (
            $input->getOption('no-interaction') ||
            $io->askQuestion($question)
        ) {
            $fixturesRoot = dirname(__DIR__) . '/../config';
            $data = Yaml::parse(file_get_contents($fixturesRoot . "/fixtures.yaml"));

            if (isset($data["importFiles"]['roles'])) {
                foreach ($data["importFiles"]['roles'] as $filename) {
                    $filePath = $fixturesRoot . "/" . $filename;
                    $this->rolesImporter->import(file_get_contents($filePath));
                    $io->success('Theme file “' . $filePath . '” has been imported.');
                }
            }
            if (isset($data["importFiles"]['groups'])) {
                foreach ($data["importFiles"]['groups'] as $filename) {
                    $filePath = $fixturesRoot . "/" . $filename;
                    $this->groupsImporter->import(file_get_contents($filePath));
                    $io->success('Theme file “' . $filePath . '” has been imported.');
                }
            }
            if (isset($data["importFiles"]['settings'])) {
                foreach ($data["importFiles"]['settings'] as $filename) {
                    $filePath = $fixturesRoot . "/" . $filename;
                    $this->settingsImporter->import(file_get_contents($filePath));
                    $io->success('Theme files “' . $filePath . '” has been imported.');
                }
            }
            $manager = $this->managerRegistry->getManagerForClass(Translation::class);
            /*
             * Create default translation
             */
            if (!$this->hasDefaultTranslation()) {
                $defaultTrans = new Translation();
                $defaultTrans
                    ->setDefaultTranslation(true)
                    ->setLocale("en")
                    ->setName("Default translation");

                $manager->persist($defaultTrans);

                $io->success('Default translation installed.');
            } else {
                $io->warning('A default translation is already installed.');
            }
            $manager->flush();

            if ($manager instanceof EntityManagerInterface) {
                // Clear result cache
                $cacheDriver = $manager->getConfiguration()->getResultCacheImpl();
                if ($cacheDriver instanceof CacheProvider) {
                    $cacheDriver->deleteAll();
                }
            }
        }
        return 0;
    }

    /**
     * Tell if there is any translation.
     *
     * @return boolean
     */
    public function hasDefaultTranslation(): bool
    {
        $default = $this->managerRegistry->getRepository(Translation::class)->findOneBy([]);
        return $default !== null;
    }
}
