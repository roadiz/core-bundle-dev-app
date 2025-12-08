<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Node;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class NodesOrphansCommand extends Command
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    #[\Override]
    protected function configure(): void
    {
        $this->setName('nodes:orphans')
            ->setDescription('Find nodes without any source attached, and delete them.')
            ->addOption(
                'delete',
                'd',
                InputOption::VALUE_NONE,
                'Delete orphans nodes.'
            );
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $entityManager = $this->managerRegistry
            ->getManagerForClass(Node::class) ?? throw new \RuntimeException('No entity manager found for Node class.');
        $qb = $entityManager->createQueryBuilder();
        $qb->select('n')
            ->from(Node::class, 'n')
            ->leftJoin('n.nodeSources', 'ns')
            ->having('COUNT(ns.id) = 0')
            ->groupBy('n');

        $orphans = [];
        try {
            $orphans = $qb->getQuery()->getResult();
        } catch (NoResultException) {
        }

        if (0 === count($orphans)) {
            $io->success('That’s OK, you don’t have any orphan node.');

            return 0;
        }

        $io->note(sprintf('You have %s orphan node(s)!', count($orphans)));
        $tableContent = [];

        /** @var Node $node */
        foreach ($orphans as $node) {
            $tableContent[] = [
                $node->getId(),
                $node->getNodeName(),
                $node->getNodeTypeName() ?? '',
                !$node->isVisible() ? 'X' : '',
                $node->isPublished() ? 'X' : '',
            ];
        }

        $io->table(['Id', 'Name', 'Type', 'Hidden', 'Published'], $tableContent);

        if ($input->getOption('delete')) {
            /** @var Node $orphan */
            foreach ($orphans as $orphan) {
                $entityManager->remove($orphan);
            }
            $entityManager->flush();

            $io->success('Orphan nodes have been removed from your database.');
        } else {
            $io->note('Use --delete option to actually remove these nodes.');
        }

        return 0;
    }
}
