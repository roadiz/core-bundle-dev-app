<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Exception\EntityAlreadyExistsException;
use RZ\Roadiz\CoreBundle\Importer\AttributeImporter;
use RZ\Roadiz\CoreBundle\Importer\EntityImporterInterface;
use RZ\Roadiz\CoreBundle\Importer\GroupsImporter;
use RZ\Roadiz\CoreBundle\Importer\NodeTypesImporter;
use RZ\Roadiz\CoreBundle\Importer\RolesImporter;
use RZ\Roadiz\CoreBundle\Importer\SettingsImporter;
use RZ\Roadiz\CoreBundle\Importer\TagsImporter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Yaml\Yaml;

/**
 * Command line utils for managing themes from terminal.
 */
final class AppInstallCommand extends Command
{
    protected SymfonyStyle $io;
    private bool $dryRun = false;
    protected string $projectDir;
    protected NodeTypesImporter $nodeTypesImporter;
    protected TagsImporter $tagsImporter;
    protected SettingsImporter $settingsImporter;
    protected RolesImporter $rolesImporter;
    protected GroupsImporter $groupsImporter;
    protected AttributeImporter $attributeImporter;
    protected ManagerRegistry $managerRegistry;

    public function __construct(
        string $projectDir,
        ManagerRegistry $managerRegistry,
        NodeTypesImporter $nodeTypesImporter,
        TagsImporter $tagsImporter,
        SettingsImporter $settingsImporter,
        RolesImporter $rolesImporter,
        GroupsImporter $groupsImporter,
        AttributeImporter $attributeImporter,
        string $name = null
    ) {
        parent::__construct($name);
        $this->projectDir = $projectDir;
        $this->nodeTypesImporter = $nodeTypesImporter;
        $this->tagsImporter = $tagsImporter;
        $this->settingsImporter = $settingsImporter;
        $this->rolesImporter = $rolesImporter;
        $this->groupsImporter = $groupsImporter;
        $this->attributeImporter = $attributeImporter;
        $this->managerRegistry = $managerRegistry;
    }

    protected function configure(): void
    {
        $this->setName('app:install')
            ->setDescription('Install application fixtures (node-types, settings, roles) from config.yml')
            ->addOption(
                'dry-run',
                'd',
                InputOption::VALUE_NONE,
                'Do nothing, only print information.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getOption('dry-run')) {
            $this->dryRun = true;
        }
        $this->io = new SymfonyStyle($input, $output);

        /*
         * Test if Classname is not a valid yaml file before using Theme
         */
        $configPath = $this->projectDir . '/src/Resources/config.yml';
        $realConfigPath = realpath($configPath);
        if (false !== $realConfigPath && file_exists($realConfigPath)) {
            $this->io->note('Install assets directly from file: ' . $realConfigPath);
            $themeConfigPath = $realConfigPath;
        } else {
            $this->io->error($configPath . ' configuration file is not readable.');
            return 1;
        }

        $this->importAppData($themeConfigPath);
        return 0;
    }

    protected function importAppData(string $themeConfigPath): void
    {
        $data = $this->getAppConfig($themeConfigPath);

        if (isset($data["importFiles"])) {
            if (isset($data["importFiles"]['groups'])) {
                foreach ($data["importFiles"]['groups'] as $filename) {
                    $this->importFile($filename, $this->groupsImporter);
                }
            }
            if (isset($data["importFiles"]['roles'])) {
                foreach ($data["importFiles"]['roles'] as $filename) {
                    $this->importFile($filename, $this->rolesImporter);
                }
            }
            if (isset($data["importFiles"]['settings'])) {
                foreach ($data["importFiles"]['settings'] as $filename) {
                    $this->importFile($filename, $this->settingsImporter);
                }
            }
            if (isset($data["importFiles"]['nodetypes'])) {
                foreach ($data["importFiles"]['nodetypes'] as $filename) {
                    $this->importFile($filename, $this->nodeTypesImporter);
                }
            }
            if (isset($data["importFiles"]['tags'])) {
                foreach ($data["importFiles"]['tags'] as $filename) {
                    $this->importFile($filename, $this->tagsImporter);
                }
            }
            if (isset($data["importFiles"]['attributes'])) {
                foreach ($data["importFiles"]['attributes'] as $filename) {
                    $this->importFile($filename, $this->attributeImporter);
                }
            }
        } else {
            $this->io->warning('Config file "' . $themeConfigPath . '" has no data to import.');
        }
    }

    /**
     * @param string $filename
     * @param EntityImporterInterface $importer
     */
    protected function importFile(string $filename, EntityImporterInterface $importer): void
    {
        if (false !== $realFilename = realpath($filename)) {
            $file = new File($realFilename);
        } else {
            throw new \RuntimeException($filename . ' is not a valid file');
        }
        if (!$this->dryRun) {
            try {
                if (false === $fileContent = file_get_contents($file->getPathname())) {
                    throw new \RuntimeException($file->getPathname() . ' file is not readable');
                }
                $importer->import($fileContent);
                $this->managerRegistry->getManager()->flush();
                $this->io->writeln(
                    '* <info>' . $file->getPathname() . '</info> file has been imported.'
                );
                return;
            } catch (EntityAlreadyExistsException $e) {
                $this->io->writeln(
                    '* <info>' . $file->getPathname() . '</info>' .
                    ' <error>has NOT been imported (' . $e->getMessage() . ')</error>.'
                );
            }
        }
        $this->io->writeln(
            '* <info>' . $file->getPathname() . '</info> file has been imported.'
        );
    }

    /**
     * @param string $appConfigPath
     * @return array
     */
    protected function getAppConfig(string $appConfigPath): array
    {
        if (false === $fileContent = file_get_contents($appConfigPath)) {
            throw new \RuntimeException($appConfigPath . ' file is not readable');
        }
        $data = Yaml::parse($fileContent);
        if (!\is_array($data)) {
            throw new \RuntimeException($appConfigPath . ' file is not a valid YAML file');
        }
        return $data;
    }
}
