<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\EntityHandler\HandlerFactory;
use RZ\Roadiz\CoreBundle\EntityHandler\NodeHandler;
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
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setName('nodes:empty-trash')
            ->setDescription('Remove definitely deleted nodes.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $em = $this->managerRegistry->getManagerForClass(Node::class);
        $countQb = $this->createNodeQueryBuilder();
        $countQuery = $countQb->select($countQb->expr()->count('n'))
            ->andWhere($countQb->expr()->eq('n.status', Node::DELETED))
            ->getQuery();
        $emptiedCount = $countQuery->getSingleScalarResult();
        if (0 == $emptiedCount) {
            $io->success('Nodes trashcan is already empty.');

            return 0;
        }

        $confirmation = new ConfirmationQuestion(
            sprintf('<question>Are you sure to empty nodes trashcan, %d nodes will be lost forever?</question> [y/N]: ', $emptiedCount),
            false
        );

        if ($input->isInteractive() && !$io->askQuestion($confirmation)) {
            return 0;
        }

        $i = 0;
        $batchSize = 100;
        $io->progressStart((int) $emptiedCount);

        $qb = $this->createNodeQueryBuilder();
        $q = $qb->select('n')
            ->andWhere($countQb->expr()->eq('n.status', Node::DELETED))
            ->getQuery();

        foreach ($q->toIterable() as $row) {
            /** @var NodeHandler $nodeHandler */
            $nodeHandler = $this->handlerFactory->getHandler($row);
            $nodeHandler->removeWithChildrenAndAssociations();
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

    protected function createNodeQueryBuilder(): QueryBuilder
    {
        return $this->managerRegistry
            ->getRepository(Node::class)
            ->createQueryBuilder('n');
    }
}
