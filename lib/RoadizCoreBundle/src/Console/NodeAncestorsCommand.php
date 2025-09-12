<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use RZ\Roadiz\CoreBundle\Repository\AllStatusesNodeRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'nodes:ancestors',
    description: 'Get all ancestors for a node.',
)]
final class NodeAncestorsCommand extends Command
{
    public function __construct(private readonly AllStatusesNodeRepository $nodeRepository, ?string $name = null)
    {
        parent::__construct($name);
    }

    #[\Override]
    protected function configure(): void
    {
        $this->addArgument('nodeId', InputArgument::REQUIRED, 'Leaf node ID.');
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $nodeId = $input->getArgument('nodeId');
        if (!\is_numeric($nodeId)) {
            throw new \InvalidArgumentException('Node ID must be an integer.');
        }

        $ancestors = $this->nodeRepository->findAllAncestors((int) $nodeId);
        $io->table(['node', 'ancestor', 'level'], $ancestors);

        return Command::SUCCESS;
    }
}
