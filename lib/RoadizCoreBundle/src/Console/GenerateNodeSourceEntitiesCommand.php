<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use RZ\Roadiz\Contracts\NodeType\NodeTypeClassLocatorInterface;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\EntityHandler\NodeTypeHandler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'generate:nsentities',
    description: 'Generate node-sources entities PHP classes in <info>src/GeneratedEntity</info>.',
)]
final class GenerateNodeSourceEntitiesCommand extends Command
{
    public function __construct(
        private readonly NodeTypes $nodeTypesBag,
        private readonly NodeTypeHandler $nodeTypeHandler,
        private readonly NodeTypeClassLocatorInterface $nodeTypeClassLocator,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $nodeTypes = $this->nodeTypesBag->all();

        if (0 === count($nodeTypes)) {
            $io->error('No available node-types…');

            return 1;
        }

        foreach ($nodeTypes as $nt) {
            $handler = $this->nodeTypeHandler->setNodeType($nt);
            $handler->removeSourceEntityClass();
            $handler->generateSourceEntityClass();
            $io->writeln('* Source class <info>'.$this->nodeTypeClassLocator->getSourceEntityClassName($nt).'</info> has been generated.');

            if ($output->isVeryVerbose()) {
                $io->writeln("\t<info>".$handler->getSourceClassPath().'</info>');
            }
        }

        return 0;
    }
}
