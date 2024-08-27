<?php

declare(strict_types=1);

namespace RZ\Roadiz\CompatBundle\Console;

use RZ\Roadiz\CompatBundle\Theme\ThemeGenerator;
use RZ\Roadiz\CompatBundle\Theme\ThemeInfo;
use RZ\Roadiz\CoreBundle\Exception\ThemeClassNotValidException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class ThemeAssetsCommand extends Command
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
        private readonly ThemeGenerator $themeGenerator
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('themes:assets:install')
            ->setDescription('Install a theme assets folder in public directory.')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'Theme name (without the "Theme" suffix) or full-qualified ThemeApp class name (you can use / instead of \\).'
            )
            ->addOption('symlink', null, InputOption::VALUE_NONE, 'Symlinks the theme assets instead of copying it')
            ->addOption('relative', null, InputOption::VALUE_NONE, 'Make relative symlinks')
        ;
    }

    /**
     * @throws ThemeClassNotValidException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        if ($input->getOption('relative')) {
            $expectedMethod = ThemeGenerator::METHOD_RELATIVE_SYMLINK;
            $io->writeln('Trying to install theme assets as <info>relative symbolic link</info>.');
        } elseif ($input->getOption('symlink')) {
            $expectedMethod = ThemeGenerator::METHOD_ABSOLUTE_SYMLINK;
            $io->writeln('Trying to install theme assets as <info>absolute symbolic link</info>.');
        } else {
            $expectedMethod = ThemeGenerator::METHOD_COPY;
            $io->writeln('Installing theme assets as <info>hard copy</info>.');
        }
        $name = str_replace('/', '\\', $input->getArgument('name'));

        $themeInfo = new ThemeInfo($name, $this->projectDir);

        if (!$themeInfo->exists()) {
            throw new InvalidArgumentException($themeInfo->getThemePath() . ' does not exist.');
        }

        $io->table([
            'Description', 'Value'
        ], [
            ['Given name', $themeInfo->getName()],
            ['Theme path', $themeInfo->getThemePath()],
            ['Assets path', $themeInfo->getThemePath() . '/static'],
        ]);

        $this->themeGenerator->installThemeAssets($themeInfo, $expectedMethod);
        return 0;
    }
}
