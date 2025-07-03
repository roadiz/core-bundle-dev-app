<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Importer\GroupsImporter;
use RZ\Roadiz\CoreBundle\Importer\SettingsImporter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Yaml\Yaml;

final class InstallCommand extends Command
{
    use RunningCommandsTrait;

    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly GroupsImporter $groupsImporter,
        private readonly SettingsImporter $settingsImporter,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    #[\Override]
    public function getProjectDir(): string
    {
        return $this->projectDir;
    }

    #[\Override]
    protected function configure(): void
    {
        $this
            ->setName('install')
            ->setDescription('Perform Doctrine migrations, install default Roadiz roles, settings and translation.');
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $question = new ConfirmationQuestion(
            '<question>Are you sure to perform installation?</question>',
            false
        );

        if (
            $input->getOption('no-interaction')
            || $io->askQuestion($question)
        ) {
            0 === $this->runCommand(
                'doctrine:migrations:migrate',
                '',
                null,
                false,
                true
            ) ? $io->success('doctrine:migrations:migrate') : $io->error('doctrine:migrations:migrate');

            $fixturesRoot = dirname(__DIR__).'/../config';
            $fixtureFile = file_get_contents($fixturesRoot.'/fixtures.yaml');

            if (false === $fixtureFile) {
                $io->error('No fixtures.yaml file found in '.$fixturesRoot);

                return 1;
            }

            $data = Yaml::parse($fixtureFile);

            if (isset($data['importFiles']['groups'])) {
                foreach ($data['importFiles']['groups'] as $filename) {
                    $filePath = $fixturesRoot.'/'.$filename;
                    $fileContents = file_get_contents($filePath);
                    if (false === $fileContents) {
                        $io->error('No file found in '.$filePath);

                        return 1;
                    }
                    $this->groupsImporter->import($fileContents);
                    $io->success('Theme file “'.$filePath.'” has been imported.');
                }
            }
            if (isset($data['importFiles']['settings'])) {
                foreach ($data['importFiles']['settings'] as $filename) {
                    $filePath = $fixturesRoot.'/'.$filename;
                    $fileContents = file_get_contents($filePath);
                    if (false === $fileContents) {
                        $io->error('No file found in '.$filePath);

                        return 1;
                    }
                    $this->settingsImporter->import($fileContents);
                    $io->success('Theme files “'.$filePath.'” has been imported.');
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
                    ->setLocale('en')
                    ->setName('Default translation');

                $manager->persist($defaultTrans);

                $io->success('Default translation installed.');
            } else {
                $io->warning('A default translation is already installed.');
            }
            $manager->flush();

            $this->clearCaches($io);
        }

        return 0;
    }

    /**
     * Tell if there is any translation.
     */
    public function hasDefaultTranslation(): bool
    {
        return null !== $this->managerRegistry->getRepository(Translation::class)->findDefault();
    }
}
