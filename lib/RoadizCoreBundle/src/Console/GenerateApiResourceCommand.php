<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\NodeType\ApiResourceGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class GenerateApiResourceCommand extends Command
{
    public function __construct(
        private readonly NodeTypes $nodeTypesBag,
        private readonly ApiResourceGenerator $apiResourceGenerator,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('generate:api-resources')
            ->setDescription('Generate node-sources entities API Platform resource files.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $nodeTypes = $this->nodeTypesBag->all();

        if (0 === count($nodeTypes)) {
            $io->error('No available node-types…');

            return 1;
        }

        foreach ($nodeTypes as $nt) {
            $resourcePath = $this->apiResourceGenerator->generate($nt);
            if (null !== $resourcePath) {
                $io->writeln('* API resource <info>'.$resourcePath.'</info> has been generated.');
            }
        }

        return 0;
    }
}
