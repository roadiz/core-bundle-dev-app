<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\EntityHandler\HandlerFactory;
use RZ\Roadiz\CoreBundle\EntityHandler\NodeTypeHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command line utils for managing node-types from terminal.
 */
class GenerateNodeSourceEntitiesCommand extends Command
{
    protected ManagerRegistry $managerRegistry;
    protected HandlerFactory $handlerFactory;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param HandlerFactory $handlerFactory
     */
    public function __construct(ManagerRegistry $managerRegistry, HandlerFactory $handlerFactory)
    {
        parent::__construct();
        $this->managerRegistry = $managerRegistry;
        $this->handlerFactory = $handlerFactory;
    }

    protected function configure(): void
    {
        $this->setName('generate:nsentities')
            ->setDescription('Generate node-sources entities PHP classes.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $nodetypes = $this->managerRegistry
            ->getRepository(NodeType::class)
            ->findAll();

        if (count($nodetypes) > 0) {
            /** @var NodeType $nt */
            foreach ($nodetypes as $nt) {
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
        } else {
            $io->error('No available node-types…');
            return 1;
        }
    }
}
