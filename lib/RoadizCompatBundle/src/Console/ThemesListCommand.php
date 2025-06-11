<?php

declare(strict_types=1);

namespace RZ\Roadiz\CompatBundle\Console;

use RZ\Roadiz\CompatBundle\Theme\ThemeResolverInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command line utils for managing themes from terminal.
 */
final class ThemesListCommand extends Command
{
    public function __construct(
        private readonly ThemeResolverInterface $themeResolver,
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this->setName('themes:list')
            ->setDescription('List installed themes')
            ->addArgument(
                'classname',
                InputArgument::OPTIONAL,
                'Main theme classname (Use / instead of \\ and do not forget starting slash)'
            );
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $name = $input->getArgument('classname');

        $tableContent = [];

        if ($name) {
            /*
             * Replace slash by anti-slashes
             */
            $name = str_replace('/', '\\', $name);
            $theme = $this->themeResolver->findThemeByClass($name);
            if (null === $theme) {
                throw new InvalidArgumentException($name.' theme cannot be found.');
            }
            $tableContent[] = [
                str_replace('\\', '/', $theme->getClassName()),
                $theme->isAvailable() ? 'X' : '',
                $theme->isBackendTheme() ? 'Backend' : 'Frontend',
            ];
        } else {
            $themes = $this->themeResolver->findAll();
            if (count($themes) > 0) {
                foreach ($themes as $theme) {
                    $tableContent[] = [
                        str_replace('\\', '/', $theme->getClassName()),
                        $theme->isAvailable() ? 'X' : '',
                        $theme->isBackendTheme() ? 'Backend' : 'Frontend',
                    ];
                }
            } else {
                $io->warning('No available themes');
            }
        }

        $io->table(['Class (with / instead of \)', 'Enabled', 'Type'], $tableContent);

        return 0;
    }
}
