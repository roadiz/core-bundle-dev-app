<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Logger\Entity\Log;
use RZ\Roadiz\CoreBundle\Repository\LogRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class LogsCleanupCommand extends Command
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setName('logs:cleanup')
            ->setDescription('Clean up logs entries <info>older than 6 months</info> from database.')
            ->addOption('erase', null, InputOption::VALUE_NONE, 'Actually delete outdated log entries.')
            ->addOption('since', null, InputOption::VALUE_REQUIRED, 'Change default deletion duration from now.')
        ;
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $now = new \DateTime('now');
        $since = '-3 months';
        if (\is_string($input->getOption('since'))) {
            $since = '-' . $input->getOption('since');
        }
        $interval = \DateInterval::createFromDateString($since);
        if (false === $interval) {
            throw new InvalidArgumentException('Invalid since option format.');
        }
        $now->add($interval);
        $io = new SymfonyStyle($input, $output);

        /** @var LogRepository $logRepository */
        $logRepository = $this->managerRegistry->getRepository(Log::class);
        $qb = $logRepository->createQueryBuilder('l');
        $qb->select($qb->expr()->count('l'))
            ->andWhere($qb->expr()->lte('l.datetime', ':date'))
            ->setParameter(':date', $now)
        ;

        try {
            $logs = $qb->getQuery()->getSingleScalarResult();
        } catch (NoResultException $e) {
            $logs = 0;
        }

        $io->note($logs . ' log entries found before ' . $now->format('Y-m-d H:i:s') . '.');

        if ($input->getOption('erase') && $logs > 0) {
            $qb2 = $logRepository->createQueryBuilder('l');
            $qb2->delete()
                ->andWhere($qb->expr()->lte('l.datetime', ':date'))
                ->setParameter(':date', $now)
            ;
            try {
                $numDeleted = $qb2->getQuery()->execute();
                $io->success($numDeleted . ' log entries were deleted.');
            } catch (NoResultException $e) {
                $io->writeln('No log entries were deleted.');
            }
        }
        return 0;
    }
}
