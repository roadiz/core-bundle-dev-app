<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use RZ\Roadiz\CoreBundle\EntityHandler\NodeTypeHandler;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @deprecated nodeTypes will be static in future Roadiz versions
 *
 * Command line utils for managing node-types from terminal
 */
final class NodeTypesAddFieldCommand extends NodeTypesCreationCommand
{
    protected function configure(): void
    {
        $this->setName('nodetypes:add-fields')
            ->setDescription('Add fields to a node-type')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'Node-type name'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $name = $input->getArgument('name');

        /** @var NodeType|null $nodeType */
        $nodeType = $this->managerRegistry
            ->getRepository(NodeType::class)
            ->findOneBy(['name' => $name]);

        if (null === $nodeType) {
            $io->error('Node-type "'.$name.'" does not exist.');

            return 1;
        }

        $latestPosition = $this->managerRegistry
            ->getRepository(NodeTypeField::class)
            ->findLatestPositionInNodeType($nodeType);
        $this->addNodeTypeField($nodeType, $latestPosition + 1, $io);
        $this->managerRegistry->getManagerForClass(NodeTypeField::class)->flush();

        /** @var NodeTypeHandler $handler */
        $handler = $this->handlerFactory->getHandler($nodeType);
        $handler->regenerateEntityClass();
        $this->schemaUpdater->updateNodeTypesSchema();

        $io->success('Node type '.$nodeType->getName().' has been updated.');

        return 0;
    }
}
