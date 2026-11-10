<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Node\UniversalDataDuplicator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

final class NodeApplyUniversalFieldsCommand extends Command
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly UniversalDataDuplicator $universalDataDuplicator,
        private readonly NodeTypes $nodeTypesBag,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    #[\Override]
    protected function configure(): void
    {
        $this->setName('nodes:force-universal')
            ->setDescription('Clean every nodes universal fields getting value form their default translation.')
        ;
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $translation = $this->managerRegistry->getRepository(Translation::class)->findDefault();
        $io = new SymfonyStyle($input, $output);

        $manager = $this->managerRegistry->getManagerForClass(NodesSources::class);
        if (null === $manager) {
            throw new \RuntimeException('No manager found for '.NodesSources::class);
        }

        /*
         * Node-types are declarative (loaded from files) since Roadiz 2.5 and are no longer
         * queryable through Doctrine. Resolve the node-type names holding at least one universal
         * field from the NodeTypes bag, then filter sources on their nodeTypeName string.
         */
        $universalNodeTypeNames = array_values(array_filter(
            array_map(
                fn (NodeType $nodeType) => $nodeType->getName(),
                array_filter(
                    $this->nodeTypesBag->all(),
                    fn (NodeType $nodeType) => $nodeType->getFields()->exists(
                        fn (int $key, NodeTypeField $field) => $field->isUniversal()
                    )
                )
            )
        ));

        if (0 === count($universalNodeTypeNames)) {
            $io->warning('No node-type with universal fields were found.');

            return 0;
        }

        $qb = $manager->createQueryBuilder();
        $qb->select('ns')
            ->distinct(true)
            ->from(NodesSources::class, 'ns')
            ->innerJoin('ns.node', 'n')
            ->andWhere($qb->expr()->eq('ns.translation', ':translation'))
            ->andWhere($qb->expr()->in('n.nodeTypeName', ':nodeTypeNames'))
            ->setParameter('translation', $translation)
            ->setParameter('nodeTypeNames', $universalNodeTypeNames);

        /** @var NodesSources[] $sources */
        $sources = $qb->getQuery()->getResult();

        if (0 === count($sources)) {
            $io->warning('No node with universal fields were found.');

            return 0;
        }

        $io->note(count($sources).' node(s) with universal fields were found.');

        $question = new ConfirmationQuestion(
            '<question>Are you sure to force every universal fields?</question>',
            false
        );
        if ($io->askQuestion($question)) {
            $io->progressStart(count($sources));

            foreach ($sources as $source) {
                $this->universalDataDuplicator->duplicateUniversalContents($source);
                $io->progressAdvance();
            }
            $manager->flush();
            $io->progressFinish();
        }

        return 0;
    }
}
