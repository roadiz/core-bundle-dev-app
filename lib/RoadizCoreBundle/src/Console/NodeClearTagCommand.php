<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

final class NodeClearTagCommand extends Command
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('nodes:clear-tag')
            ->addArgument('tagId', InputArgument::REQUIRED, 'Tag ID to delete nodes from.')
            ->setDescription('Delete every Nodes linked with a given Tag. <info>Danger zone</info>')
        ;
    }

    protected function getNodeQueryBuilder(Tag $tag): QueryBuilder
    {
        $qb = $this->managerRegistry->getRepository(Node::class)->createQueryBuilder('n');
        return $qb->innerJoin('n.nodesTags', 'ntg')
            ->andWhere($qb->expr()->eq('ntg.tag', ':tagId'))
            ->setParameter(':tagId', $tag);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $em = $this->managerRegistry->getManagerForClass(Node::class);
        $io = new SymfonyStyle($input, $output);

        $tagId = (int) $input->getArgument('tagId');
        if ($tagId <= 0) {
            throw new \InvalidArgumentException('Tag ID must be a valid ID');
        }
        /** @var Tag|null $tag */
        $tag = $em->find(Tag::class, $tagId);
        if ($tag === null) {
            throw new \InvalidArgumentException(sprintf('Tag #%d does not exist.', $tagId));
        }

        $batchSize = 20;
        $i = 0;

        $count = (int) $this->getNodeQueryBuilder($tag)
            ->select('count(n)')
            ->getQuery()
            ->getSingleScalarResult();

        if ($count <= 0) {
            $io->warning('No nodes were found linked with this tag.');
            return 0;
        }

        if (
            $io->askQuestion(new ConfirmationQuestion(
                sprintf('Are you sure to delete permanently %d nodes?', $count),
                false
            ))
        ) {
            $results = $this->getNodeQueryBuilder($tag)
                ->select('n')
                ->getQuery()
                ->getResult();

            $io->progressStart($count);
            /** @var Node $node */
            foreach ($results as $node) {
                $em->remove($node);
                if (($i % $batchSize) === 0) {
                    $em->flush(); // Executes all updates.
                }
                ++$i;
                $io->progressAdvance();
            }
            $em->flush();
            $io->progressFinish();
        }

        return 0;
    }
}
