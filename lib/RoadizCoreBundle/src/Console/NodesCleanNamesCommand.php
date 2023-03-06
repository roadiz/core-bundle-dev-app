<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Node\NodeNamePolicyInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @package RZ\Roadiz\CoreBundle\Console
 */
final class NodesCleanNamesCommand extends Command
{
    protected NodeNamePolicyInterface $nodeNamePolicy;
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
        $this->setName('nodes:clean-names')
            ->setDescription('Clean every nodes names according to their default node-source title.')
            ->addOption(
                'use-date',
                null,
                InputOption::VALUE_NONE,
                'Use date instead of uniqid.'
            )
            ->addOption(
                'dry-run',
                'd',
                InputOption::VALUE_NONE,
                'Do nothing, only print information.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $entityManager = $this->managerRegistry->getManagerForClass(Node::class);
        $io = new SymfonyStyle($input, $output);

        $translation = $entityManager
            ->getRepository(Translation::class)
            ->findDefault();

        if (null !== $translation) {
            /** @phpstan-ignore-next-line  */
            $nodes = $entityManager
                ->getRepository(Node::class)
                ->setDisplayingNotPublishedNodes(true)
                ->findBy([
                    'dynamicNodeName' => true,
                    'locked' => false,
                    'translation' => $translation,
                ]);

            $io->note(
                'This command will rename EVERY nodes (except for locked and not dynamic ones) names according to their node-source for current default translation.' . PHP_EOL .
                count($nodes) . ' nodes might be affected.'
            );

            $question1 = new ConfirmationQuestion('<question>Are you sure to proceed? This could break many page URLs!</question>', false);

            if ($io->askQuestion($question1)) {
                $io->note('Renaming ' . count($nodes) . ' nodes…');
                $renameCount = 0;
                $names = [];

                /** @var Node $node */
                foreach ($nodes as $node) {
                    $nodeSource = $node->getNodeSources()->first() ?: null;
                    if ($nodeSource !== null) {
                        $prefixName = $nodeSource->getTitle() != "" ?
                            $nodeSource->getTitle() :
                            $node->getNodeName();

                        $prefixNameSlug = $this->nodeNamePolicy->getCanonicalNodeName($nodeSource);
                        /*
                         * Proceed to rename only if best name is not the current
                         * node-name AND if it is not ALREADY suffixed with a unique ID.
                         */
                        if (
                            $prefixNameSlug != $node->getNodeName() &&
                            $this->nodeNamePolicy->isNodeNameValid($prefixNameSlug) &&
                            !$this->nodeNamePolicy->isNodeNameWithUniqId($prefixNameSlug, $nodeSource->getNode()->getNodeName())
                        ) {
                            $alreadyUsed = $this->nodeNamePolicy->isNodeNameAlreadyUsed($prefixNameSlug);
                            if (!$alreadyUsed) {
                                $names[] = [
                                    $node->getNodeName(),
                                    $prefixNameSlug
                                ];
                                $node->setNodeName($prefixNameSlug);
                            } else {
                                if (
                                    $input->getOption('use-date') &&
                                    null !== $nodeSource->getPublishedAt()
                                ) {
                                    $suffixedNameSlug = $this->nodeNamePolicy->getDatestampedNodeName($nodeSource);
                                } else {
                                    $suffixedNameSlug = $this->nodeNamePolicy->getSafeNodeName($nodeSource);
                                }
                                if (!$this->nodeNamePolicy->isNodeNameAlreadyUsed($suffixedNameSlug)) {
                                    $names[] = [
                                        $node->getNodeName(),
                                        $suffixedNameSlug
                                    ];
                                    $node->setNodeName($suffixedNameSlug);
                                } else {
                                    $suffixedNameSlug = $this->nodeNamePolicy->getSafeNodeName($nodeSource);
                                    $names[] = [
                                        $node->getNodeName(),
                                        $suffixedNameSlug
                                    ];
                                    $node->setNodeName($suffixedNameSlug);
                                }
                            }
                            if (!$input->getOption('dry-run')) {
                                $entityManager->flush();
                            }
                            $renameCount++;
                        }
                    }
                }

                $io->table(['Old name', 'New name'], $names);

                if (!$input->getOption('dry-run')) {
                    $io->success('Renaming done! ' . $renameCount . ' nodes have been affected. Do not forget to reindex your Solr documents if you are using it.');
                } else {
                    $io->success($renameCount . ' nodes would have been affected. Nothing was saved to database.');
                }
            } else {
                $io->warning('Renaming cancelled…');
                return 1;
            }
        }

        return 0;
    }
}
