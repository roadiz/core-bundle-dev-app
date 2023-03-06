<?php

declare(strict_types=1);

namespace RZ\Roadiz\CompatBundle\Console;

use RZ\Roadiz\CompatBundle\Theme\ThemeGenerator;
use RZ\Roadiz\CompatBundle\Theme\ThemeInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ThemeInfoCommand extends Command
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
        $this->setName('themes:info')
            ->setDescription('Get information from a Theme.')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'Theme name (without the "Theme" suffix) or full-qualified ThemeApp class name (you can use / instead of \\).'
            )
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
        $name = str_replace('/', '\\', $input->getArgument('name'));
        $themeInfo = new ThemeInfo($name, $this->projectDir);

        if ($themeInfo->exists()) {
            if (!$themeInfo->isValid()) {
                throw new InvalidArgumentException($themeInfo->getClassname() . ' is not a valid theme.');
            }
            $io->table([
                'Description', 'Value'
            ], [
                ['Given name', $themeInfo->getName()],
                ['Theme classname', $themeInfo->getClassname()],
                ['Theme path', $themeInfo->getThemePath()],
                ['Assets path', $themeInfo->getThemePath() . '/static'],
            ]);
            return 0;
        }
        throw new InvalidArgumentException($themeInfo->getClassname() . ' does not exist.');
    }
}
