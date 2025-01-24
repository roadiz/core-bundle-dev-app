<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class NodeTypesCommand extends Command
{
    public function __construct(
        private readonly NodeTypes $nodeTypesBag,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('nodetypes:list')
            ->setDescription('List available node-types or fields for a given node-type name')
            ->addArgument(
                'name',
                InputArgument::OPTIONAL,
                'Node-type name'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $name = $input->getArgument('name');

        if ($name) {
            $nodeType = $this->nodeTypesBag->get($name);

            if (!$nodeType instanceof NodeType) {
                $io->note($name.' node type does not exist.');

                return 0;
            }

            $tableContent = [];
            foreach ($nodeType->getFields() as $field) {
                $tableContent[] = [
                    $field->getLabel(),
                    $field->getName(),
                    str_replace('.type', '', $field->getTypeName()),
                    $field->isVisible() ? 'X' : '',
                    $field->isIndexed() ? 'X' : '',
                ];
            }
            $io->table(['Label', 'Name', 'Type', 'Visible', 'Index'], $tableContent);
        } else {
            $nodetypes = $this->nodeTypesBag->all();

            if (0 === count($nodetypes)) {
                $io->note('No available node-types…');
            }

            $tableContent = [];

            foreach ($nodetypes as $nt) {
                $tableContent[] = [
                    $nt->getName(),
                    $nt->isVisible() ? 'X' : '',
                ];
            }

            $io->table(['Title', 'Visible'], $tableContent);
        }

        return 0;
    }
}
