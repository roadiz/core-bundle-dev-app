<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\EntityHandler\HandlerFactory;
use RZ\Roadiz\CoreBundle\EntityHandler\NodeHandler;
use RZ\Roadiz\CoreBundle\Repository\AllStatusesNodesSourcesRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

final class NodesEmptyTrashCommand extends Command
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly HandlerFactory $handlerFactory,
        private readonly AllStatusesNodesSourcesRepository $nodesSourcesRepository,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    #[\Override]
    protected function configure(): void
    {
        $this
            ->setName('nodes:empty-trash')
            ->setDescription('Remove definitely deleted nodes-sources.')
        ;
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $emptiedCount = $this->nodesSourcesRepository->countDeleted();
        if (0 == $emptiedCount) {
            $io->success('Nodes-sources trashcan is already empty.');

            return 0;
        }

        $confirmation = new ConfirmationQuestion(
            sprintf('<question>Are you sure to empty nodes-sources trashcan, %d nodes-sources will be lost forever?</question> [y/N]: ', $emptiedCount),
            false
        );

        if ($input->isInteractive() && !$io->askQuestion($confirmation)) {
            return 0;
        }

        $i = 0;
        $batchSize = 100;
        $io->progressStart((int) $emptiedCount);

        $em = $this->managerRegistry->getManagerForClass(NodesSources::class);
        $q = $this->nodesSourcesRepository->findAllDeletedQuery();

        /** @var NodesSources $row */
        foreach ($q->toIterable() as $row) {
            $node = $row->getNode();
            /*
             * If all nodes-sources are deleted for one node,
             * delete the node and all its associations.
             */
            if ($node->isDeleted()) {
                /** @var NodeHandler $nodeHandler */
                $nodeHandler = $this->handlerFactory->getHandler($row);
                $nodeHandler->removeWithChildrenAndAssociations();
            } else {
                // Otherwise, just delete this nodes-source
                $em->remove($row);
            }

            $io->progressAdvance();
            ++$i;
            // Call flush time to times
            if (($i % $batchSize) === 0) {
                $em->flush();
                $em->clear();
            }
        }

        /*
         * Final flush
         */
        $em->flush();
        $io->progressFinish();
        $io->success('Nodes trashcan has been emptied.');

        return 0;
    }
}
