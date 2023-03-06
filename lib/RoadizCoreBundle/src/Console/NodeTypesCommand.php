<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command line utils for managing node-types from terminal.
 */
class NodeTypesCommand extends Command
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
            $nodetype = $this->managerRegistry
                ->getRepository(NodeType::class)
                ->findOneByName($name);

            if ($nodetype !== null) {
                /** @var array<NodeTypeField> $fields */
                $fields = $this->managerRegistry->getRepository(NodeTypeField::class)
                    ->findBy([
                        'nodeType' => $nodetype,
                    ], ['position' => 'ASC']);

                $tableContent = [];
                foreach ($fields as $field) {
                    $tableContent[] = [
                        $field->getId(),
                        $field->getLabel(),
                        $field->getName(),
                        str_replace('.type', '', $field->getTypeName()),
                        ($field->isVisible() ? 'X' : ''),
                        ($field->isIndexed() ? 'X' : ''),
                    ];
                }
                $io->table(['Id', 'Label', 'Name', 'Type', 'Visible', 'Index'], $tableContent);
            } else {
                $io->note($name . ' node type does not exist.');
                return 0;
            }
        } else {
            /** @var array<NodeType> $nodetypes */
            $nodetypes = $this->managerRegistry
                ->getRepository(NodeType::class)
                ->findBy([], ['name' => 'ASC']);

            if (count($nodetypes) > 0) {
                $tableContent = [];

                foreach ($nodetypes as $nt) {
                    $tableContent[] = [
                        $nt->getId(),
                        $nt->getName(),
                        ($nt->isVisible() ? 'X' : ''),
                    ];
                }

                $io->table(['Id', 'Title', 'Visible'], $tableContent);
            } else {
                $io->note('No available node-types…');
            }
        }
        return 0;
    }
}
