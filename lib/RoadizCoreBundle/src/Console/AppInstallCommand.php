<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Exception\EntityAlreadyExistsException;
use RZ\Roadiz\CoreBundle\Importer\AttributeImporter;
use RZ\Roadiz\CoreBundle\Importer\EntityImporterInterface;
use RZ\Roadiz\CoreBundle\Importer\GroupsImporter;
use RZ\Roadiz\CoreBundle\Importer\SettingsImporter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Yaml\Yaml;

final class AppInstallCommand extends Command
{
    private SymfonyStyle $io;
    private bool $dryRun = false;

    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
        private readonly ManagerRegistry $managerRegistry,
        private readonly SettingsImporter $settingsImporter,
        private readonly GroupsImporter $groupsImporter,
        private readonly AttributeImporter $attributeImporter,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    #[\Override]
    protected function configure(): void
    {
        $this->setName('app:install')
            ->setDescription('Install application fixtures (settings, groups, attributes) from config.yml')
            ->addOption(
                'dry-run',
                'd',
                InputOption::VALUE_NONE,
                'Do nothing, only print information.'
            );
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getOption('dry-run')) {
            $this->dryRun = true;
        }
        $this->io = new SymfonyStyle($input, $output);

        $configPath = $this->projectDir.'/src/Resources/config.yml';
        $realConfigPath = realpath($configPath);
        if (false === $realConfigPath || !(new Filesystem())->exists($realConfigPath)) {
            $this->io->note('No configuration file found in: '.$configPath);

            return 0;
        }

        $this->io->note('Install project fixtures from configuration file: '.$realConfigPath);
        $configurationPath = $realConfigPath;
        $this->importAppData($configurationPath);

        return 0;
    }

    protected function importAppData(string $configurationPath): void
    {
        $data = $this->getAppConfig($configurationPath);

        if (!isset($data['importFiles']) || !is_array($data['importFiles'])) {
            $this->io->warning('Config file "'.$configurationPath.'" has no data to import.');

            return;
        }

        if (isset($data['importFiles']['groups'])) {
            foreach ($data['importFiles']['groups'] as $filename) {
                $this->importFile($filename, $this->groupsImporter);
            }
        }
        if (isset($data['importFiles']['settings'])) {
            foreach ($data['importFiles']['settings'] as $filename) {
                $this->importFile($filename, $this->settingsImporter);
            }
        }
        if (isset($data['importFiles']['attributes'])) {
            foreach ($data['importFiles']['attributes'] as $filename) {
                $this->importFile($filename, $this->attributeImporter);
            }
        }
    }

    protected function importFile(string $filename, EntityImporterInterface $importer): void
    {
        $filesystem = new Filesystem();
        if (false !== $realFilename = realpath($filename)) {
            $file = new File($realFilename);
        } else {
            throw new \RuntimeException($filename.' is not a valid file');
        }
        if ($this->dryRun) {
            $this->io->writeln(
                '* <info>'.$file->getPathname().'</info> file would be imported.'
            );

            return;
        }

        try {
            $fileContent = $filesystem->readFile($file->getPathname());
            $importer->import($fileContent);
            $this->managerRegistry->getManager()->flush();
            $this->io->writeln(
                '* <info>'.$file->getPathname().'</info> file has been imported.'
            );

            return;
        } catch (EntityAlreadyExistsException $e) {
            $this->io->writeln(
                '* <info>'.$file->getPathname().'</info>'.
                ' <error>has NOT been imported ('.$e->getMessage().')</error>.'
            );
        }

        $this->io->writeln(
            '* <info>'.$file->getPathname().'</info> file has been imported.'
        );
    }

    protected function getAppConfig(string $appConfigPath): array
    {
        $fileContent = (new Filesystem())->readFile($appConfigPath);
        $data = Yaml::parse($fileContent);
        if (!\is_array($data)) {
            throw new \RuntimeException($appConfigPath.' file is not a valid YAML file');
        }

        return $data;
    }
}
