<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use RZ\Roadiz\Documents\Models\FileAwareInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\String\Slugger\AsciiSlugger;
use ZipArchive;

class FilesExportCommand extends Command
{
    use FilesCommandTrait;

    protected FileAwareInterface $fileAware;
    protected string $exportDir;
    protected string $appNamespace;

    public function __construct(FileAwareInterface $fileAware, string $exportDir, string $appNamespace)
    {
        parent::__construct();
        $this->fileAware = $fileAware;
        $this->exportDir = $exportDir;
        $this->appNamespace = $appNamespace;
    }

    protected function configure(): void
    {
        $this
            ->setName('files:export')
            ->setDescription('Export public files, private files and fonts into a single ZIP archive at root dir.');
    }

    /**
     * @param string $appName
     * @return string
     */
    protected function getArchiveFileName(string $appName = "files_export"): string
    {
        return $appName . '_' . date('Y-m-d') . '.zip';
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fs = new Filesystem();

        $publicFileFolder = $this->fileAware->getPublicFilesPath();
        $privateFileFolder = $this->fileAware->getPrivateFilesPath();
        $fontFileFolder = $this->fileAware->getFontsFilesPath();

        $archiveName = $this->getArchiveFileName((new AsciiSlugger())->slug($this->appNamespace, '_')->toString());
        $archivePath = $this->exportDir . DIRECTORY_SEPARATOR . $archiveName;

        if (!$fs->exists($this->exportDir)) {
            throw new \RuntimeException($archivePath . ': directory does not exist or is not writable');
        }

        $zip = new ZipArchive();
        $zip->open($archivePath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($fs->exists($publicFileFolder)) {
            $this->zipFolder($zip, $publicFileFolder, $this->getPublicFolderName());
        }
        if ($fs->exists($privateFileFolder)) {
            $this->zipFolder($zip, $privateFileFolder, $this->getPrivateFolderName());
        }
        if ($fs->exists($fontFileFolder)) {
            $this->zipFolder($zip, $fontFileFolder, $this->getFontsFolderName());
        }

        // Zip archive will be created only after closing object
        $zip->close();
        return 0;
    }


    /**
     * @param ZipArchive $zip
     * @param string $folder
     * @param string $prefix
     */
    protected function zipFolder(ZipArchive $zip, string $folder, string $prefix = "/public"): void
    {
        $finder = new Finder();
        $files = $finder->files()
            ->in($folder)
            ->ignoreDotFiles(false)
            ->exclude(['fonts', 'private']);

        /**
         * @var SplFileInfo $file
         */
        foreach ($files as $file) {
            // Skip directories (they would be added automatically)
            if (!$file->isDir()) {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($folder) + 1);

                // Add current file to archive
                $zip->addFile($filePath, $prefix . '/' . $relativePath);
            }
        }
    }
}
