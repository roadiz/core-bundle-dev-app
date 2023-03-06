<?php

declare(strict_types=1);

namespace RZ\Roadiz\CompatBundle\Console;

use RZ\Roadiz\CompatBundle\Theme\ThemeGenerator;
use RZ\Roadiz\CompatBundle\Theme\ThemeInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ThemeGenerateCommand extends Command
{
    protected string $projectDir;
    protected ThemeGenerator $themeGenerator;

    public function __construct(string $projectDir, ThemeGenerator $themeGenerator)
    {
        parent::__construct();
        $this->projectDir = $projectDir;
        $this->themeGenerator = $themeGenerator;
    }

    protected function configure(): void
    {
        $this->setName('themes:generate')
            ->setDescription('Generate a new theme based on BaseTheme boilerplate. <info>Requires "find", "sed" and "git" commands.</info>')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'Theme name (without the "Theme" suffix)'
            )
            ->addOption(
                'develop',
                'd',
                InputOption::VALUE_NONE,
                'Use BaseTheme develop branch instead of master.'
            )
            ->addOption(
                'branch',
                'b',
                InputOption::VALUE_REQUIRED,
                'Choose BaseTheme branch.'
            )
            ->addOption('symlink', null, InputOption::VALUE_NONE, 'Symlinks the theme assets instead of copying it')
            ->addOption('relative', null, InputOption::VALUE_NONE, 'Make relative symlinks')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $branch = 'master';
        if ($input->getOption('develop')) {
            $branch = 'develop';
        }
        if ($input->getOption('branch')) {
            $branch = $input->getOption('branch');
        }
        if ($input->getOption('relative')) {
            $expectedMethod = ThemeGenerator::METHOD_RELATIVE_SYMLINK;
        } elseif ($input->getOption('symlink')) {
            $expectedMethod = ThemeGenerator::METHOD_ABSOLUTE_SYMLINK;
        } else {
            $expectedMethod = ThemeGenerator::METHOD_COPY;
        }

        $name = str_replace('/', '\\', $input->getArgument('name'));
        $themeInfo = new ThemeInfo($name, $this->projectDir);

        if (
            $io->confirm(
                'Are you sure you want to generate a new theme called: "' . $themeInfo->getThemeName() . '"' .
                ' using ' . $branch . ' branch and installing its assets with ' . $expectedMethod . ' method?',
                false
            )
        ) {
            if (!$themeInfo->exists()) {
                $this->themeGenerator->downloadTheme($themeInfo, $branch);
                $io->success('BaseTheme cloned into ' . $themeInfo->getThemePath());
            }

            $this->themeGenerator->renameTheme($themeInfo);
            $this->themeGenerator->installThemeAssets($themeInfo, $expectedMethod);

            $io->note([
                'Register your theme into your config/packages/roadiz_core.yaml configuration file',
                '---',
                'themes:',
                '    - classname: ' . $themeInfo->getClassname(),
            ]);
            $io->success($themeInfo->getThemeName() . ' has been regenerated and is ready to be installed, have fun!');
        }

        return 0;
    }
}
