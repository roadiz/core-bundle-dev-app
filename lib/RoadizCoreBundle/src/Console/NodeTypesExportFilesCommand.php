<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Doctrine\ORM\EntityNotFoundException;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\NodeType\NodesTypesFilesExporter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class NodeTypesExportFilesCommand extends Command
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly NodesTypesFilesExporter $nodesTypesGenerator,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('nodetypes:export-files')
            ->setDescription('Migrate database node-types to YAML files.')
            ->addArgument('node-type', InputArgument::OPTIONAL, 'Only export specified node type.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            if ($nodeType = $input->getArgument('node-type')) {
                $nodeTypes = $this->managerRegistry
                    ->getRepository(NodeType::class)
                    ->findBy(['name' => $nodeType])
                ;
            } else {
                /** @var NodeType[] $nodeTypes */
                $nodeTypes = $this->managerRegistry
                    ->getRepository(NodeType::class)
                    ->findAll();
            }

            if (0 === count($nodeTypes)) {
                $io->error('No available node-types…');

                return Command::SUCCESS;
            }

            foreach ($nodeTypes as $nt) {
                $nodesTypesPath = $this->nodesTypesGenerator->generate($nt);
                if (null !== $nodesTypesPath) {
                    $io->writeln('* Node Type <info>'.$nodesTypesPath.'</info> has been generated.');
                }
            }

            return Command::SUCCESS;
        } catch (EntityNotFoundException) {
            $io->warning('You already use YAML files for your node-types.');

            return Command::SUCCESS;
        }
    }
}
