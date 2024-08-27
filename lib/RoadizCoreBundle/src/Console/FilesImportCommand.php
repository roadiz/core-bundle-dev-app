<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use RZ\Roadiz\Documents\Models\FileAwareInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\String\Slugger\AsciiSlugger;
use ZipArchive;

final class FilesImportCommand extends Command
{
    use FilesCommandTrait;

    public function __construct(
        private readonly FileAwareInterface $fileAware,
        private readonly string $appNamespace,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setName('files:import')
            ->setDescription('Import public files, private files and fonts from a single ZIP archive.')
            ->setDefinition([
                new InputArgument('input', InputArgument::REQUIRED, 'ZIP file path to import.'),
            ]);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $confirmation = new ConfirmationQuestion(
            '<question>Are you sure to import files from this archive? Your existing files will be lost!</question>',
            false
        );

        $appNamespace = (new AsciiSlugger())->slug($this->appNamespace, '_');
        $tempDir = tempnam(sys_get_temp_dir(), $appNamespace . '_files');
        if (false === $tempDir) {
            throw new \RuntimeException('Cannot create temporary directory.');
        }
        if (file_exists($tempDir)) {
            unlink($tempDir);
        }
        mkdir($tempDir);

        $zipArchivePath = $input->getArgument('input');
        $zip = new ZipArchive();
        if (true !== $zip->open($zipArchivePath)) {
            $io->error('Zip archive does not exist or is invalid.');
            return 1;
        }

        if (!$io->askQuestion($confirmation)) {
            return 0;
        }

        $zip->extractTo($tempDir);

        $fs = new Filesystem();
        if ($fs->exists($tempDir . $this->getPublicFolderName())) {
            $fs->mirror($tempDir . $this->getPublicFolderName(), $this->fileAware->getPublicFilesPath());
            $io->success('Public files have been imported.');
        }
        if ($fs->exists($tempDir . $this->getPrivateFolderName())) {
            $fs->mirror($tempDir . $this->getPrivateFolderName(), $this->fileAware->getPrivateFilesPath());
            $io->success('Private files have been imported.');
        }
        if ($fs->exists($tempDir . $this->getFontsFolderName())) {
            $fs->mirror($tempDir . $this->getFontsFolderName(), $this->fileAware->getFontsFilesPath());
            $io->success('Font files have been imported.');
        }

        $fs->remove($tempDir);
        return 0;
    }
}
