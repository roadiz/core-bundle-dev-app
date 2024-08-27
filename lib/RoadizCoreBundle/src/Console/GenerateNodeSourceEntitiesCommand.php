<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\EntityHandler\HandlerFactory;
use RZ\Roadiz\CoreBundle\EntityHandler\NodeTypeHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class GenerateNodeSourceEntitiesCommand extends Command
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly HandlerFactory $handlerFactory,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('generate:nsentities')
            ->setDescription('Generate node-sources entities PHP classes.');
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $nodeTypes = $this->managerRegistry
            ->getRepository(NodeType::class)
            ->findAll();

        if (count($nodeTypes) === 0) {
            $io->error('No available node-types…');
            return 1;
        }

        /** @var NodeType $nt */
        foreach ($nodeTypes as $nt) {
            /** @var NodeTypeHandler $handler */
            $handler = $this->handlerFactory->getHandler($nt);
            $handler->removeSourceEntityClass();
            $handler->generateSourceEntityClass();
            $io->writeln("* Source class <info>" . $nt->getSourceEntityClassName() . "</info> has been generated.");

            if ($output->isVeryVerbose()) {
                $io->writeln("\t<info>" . $handler->getSourceClassPath() . "</info>");
            }
        }
        return 0;
    }
}
