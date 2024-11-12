<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Doctrine\SchemaUpdater;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\EntityHandler\HandlerFactory;
use RZ\Roadiz\CoreBundle\EntityHandler\NodeTypeHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command line utils for managing node-types from terminal.
 */
final class NodeTypesDeleteCommand extends Command
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly HandlerFactory $handlerFactory,
        private readonly SchemaUpdater $schemaUpdater,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('nodetypes:delete')
            ->setDescription('Delete a node-type')
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

        if (empty($name)) {
            throw new \InvalidArgumentException('Name must not be empty.');
        }

        /** @var NodeType|null $nodeType */
        $nodeType = $this->managerRegistry
            ->getRepository(NodeType::class)
            ->findOneByName($name);

        if (null === $nodeType) {
            $io->error('"'.$name.'" node type does not exist');

            return 1;
        }

        $io->note('///////////////////////////////'.PHP_EOL.
            '/////////// WARNING ///////////'.PHP_EOL.
            '///////////////////////////////'.PHP_EOL.
            'This operation cannot be undone.'.PHP_EOL.
            'Deleting a node-type, you will automatically delete every nodes of this type.');
        $question = new ConfirmationQuestion(
            '<question>Are you sure to delete '.$nodeType->getName().' node-type?</question>',
            false
        );
        if (
            $io->askQuestion(
                $question
            )
        ) {
            /** @var NodeTypeHandler $handler */
            $handler = $this->handlerFactory->getHandler($nodeType);
            $handler->removeSourceEntityClass();
            $this->managerRegistry->getManagerForClass(NodeType::class)->remove($nodeType);
            $this->managerRegistry->getManagerForClass(NodeType::class)->flush();
            $this->schemaUpdater->updateNodeTypesSchema();
            $io->success('Node-type deleted.');
        }

        return 0;
    }
}
