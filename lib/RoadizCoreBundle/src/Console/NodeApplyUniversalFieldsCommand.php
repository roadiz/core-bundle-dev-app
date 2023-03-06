<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Node\UniversalDataDuplicator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

class NodeApplyUniversalFieldsCommand extends Command
{
    protected ManagerRegistry $managerRegistry;
    protected UniversalDataDuplicator $universalDataDuplicator;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param UniversalDataDuplicator $universalDataDuplicator
     */
    public function __construct(ManagerRegistry $managerRegistry, UniversalDataDuplicator $universalDataDuplicator)
    {
        parent::__construct();
        $this->managerRegistry = $managerRegistry;
        $this->universalDataDuplicator = $universalDataDuplicator;
    }

    protected function configure(): void
    {
        $this->setName('nodes:force-universal')
            ->setDescription('Clean every nodes universal fields getting value form their default translation.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $translation = $this->managerRegistry->getRepository(Translation::class)->findDefault();
        $io = new SymfonyStyle($input, $output);

        $manager = $this->managerRegistry->getManagerForClass(NodesSources::class);
        if (null === $manager) {
            throw new \RuntimeException('No manager found for ' . NodesSources::class);
        }

        $qb = $manager->createQueryBuilder();
        $qb->select('ns')
            ->distinct(true)
            ->from(NodesSources::class, 'ns')
            ->innerJoin('ns.node', 'n')
            ->innerJoin('n.nodeType', 'nt')
            ->innerJoin('nt.fields', 'ntf')
            ->andWhere($qb->expr()->eq('ns.translation', ':translation'))
            ->andWhere($qb->expr()->eq('ntf.universal', true))
            ->setParameter(':translation', $translation);
        try {
            $sources = $qb->getQuery()->getResult();
            $io->note(count($sources) . ' node(s) with universal fields were found.');

            $question = new ConfirmationQuestion(
                '<question>Are you sure to force every universal fields?</question>',
                false
            );
            if (
                $io->askQuestion(
                    $question
                )
            ) {
                $io->progressStart(count($sources));

                /** @var NodesSources $source */
                foreach ($sources as $source) {
                    $this->universalDataDuplicator->duplicateUniversalContents($source);
                    $io->progressAdvance();
                }
                $manager->flush();
                $io->progressFinish();
            }
        } catch (NoResultException $e) {
            $io->warning('No node with universal fields were found.');
        }
        return 0;
    }
}
