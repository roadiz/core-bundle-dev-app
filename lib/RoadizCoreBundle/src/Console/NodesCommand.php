<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Repository\NotPublishedNodeRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class NodesCommand extends Command
{
    public function __construct(
        private readonly NodeTypes $nodeTypesBag,
        private readonly NotPublishedNodeRepository $notPublishedNodeRepository,
        ?string $name = null,
    ) {
        parent::__construct($name);
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
            $nodeType = $this->nodeTypesBag->get($input->getOption('type'));
            if (null !== $nodeType) {
                $nodes = $this->notPublishedNodeRepository
                    ->findBy(['nodeTypeName' => $nodeType->getName()], ['nodeName' => 'ASC']);
            }
        } else {
            $nodes = $this->notPublishedNodeRepository
                ->findBy([], ['nodeName' => 'ASC']);
        }

        /** @var Node $node */
        foreach ($nodes as $node) {
            $tableContent[] = [
                $node->getId(),
                $node->getNodeName(),
                $node->getNodeTypeName(),
                !$node->isVisible() ? 'X' : '',
                $node->isPublished() ? 'X' : '',
            ];
        }

        $io->table(['Id', 'Name', 'Type', 'Hidden', 'Published'], $tableContent);

        return 0;
    }
}
