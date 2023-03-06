<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command line utils for managing nodes from terminal.
 */
class NodesCommand extends Command
{
    protected ManagerRegistry $managerRegistry;

    /**
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct();
        $this->managerRegistry = $managerRegistry;
    }

    protected function configure(): void
    {
        $this->setName('nodes:list')
            ->setDescription('List available nodes')
            ->addOption(
                'type',
                't',
                InputOption::VALUE_REQUIRED,
                'Filter by node-type name'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $nodes = [];
        $tableContent = [];

        if ($input->getOption('type')) {
            $nodeType = $this->managerRegistry
                ->getRepository(NodeType::class)
                ->findByName($input->getOption('type'));
            if (null !== $nodeType) {
                $nodes = $this->managerRegistry
                    ->getRepository(Node::class)
                    ->setDisplayingNotPublishedNodes(true)
                    ->findBy(['nodeType' => $nodeType], ['nodeName' => 'ASC']);
            }
        } else {
            $nodes = $this->managerRegistry
                ->getRepository(Node::class)
                ->setDisplayingNotPublishedNodes(true)
                ->findBy([], ['nodeName' => 'ASC']);
        }

        /** @var Node $node */
        foreach ($nodes as $node) {
            $tableContent[] = [
                $node->getId(),
                $node->getNodeName(),
                $node->getNodeType()->getName(),
                (!$node->isVisible() ? 'X' : ''),
                ($node->isPublished() ? 'X' : ''),
            ];
        }

        $io->table(['Id', 'Name', 'Type', 'Hidden', 'Published'], $tableContent);
        return 0;
    }
}
