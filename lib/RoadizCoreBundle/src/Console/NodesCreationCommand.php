<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Node\NodeFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command line utils for managing nodes from terminal.
 */
class NodesCreationCommand extends Command
{
    protected SymfonyStyle $io;
    protected NodeFactory $nodeFactory;
    protected ManagerRegistry $managerRegistry;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param NodeFactory $nodeFactory
     */
    public function __construct(ManagerRegistry $managerRegistry, NodeFactory $nodeFactory)
    {
        parent::__construct();
        $this->managerRegistry = $managerRegistry;
        $this->nodeFactory = $nodeFactory;
    }

    protected function configure(): void
    {
        $this->setName('nodes:create')
            ->setDescription('Create a new node')
            ->addArgument(
                'node-name',
                InputArgument::REQUIRED,
                'Node name'
            )
            ->addArgument(
                'node-type',
                InputArgument::REQUIRED,
                'Node-type name'
            )
            ->addArgument(
                'locale',
                InputArgument::OPTIONAL,
                'Translation locale'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $nodeName = $input->getArgument('node-name');
        $typeName = $input->getArgument('node-type');
        $locale = $input->getArgument('locale');
        $this->io = new SymfonyStyle($input, $output);

        $existingNode = $this->managerRegistry
            ->getRepository(Node::class)
            ->setDisplayingNotPublishedNodes(true)
            ->findOneByNodeName($nodeName);

        if (null === $existingNode) {
            $type = $this->managerRegistry
                ->getRepository(NodeType::class)
                ->findOneByName($typeName);

            if (null !== $type) {
                $translation = null;

                if ($locale) {
                    $translation = $this->managerRegistry
                        ->getRepository(TranslationInterface::class)
                        ->findOneBy(['locale' => $locale]);
                }

                if ($translation === null) {
                    $translation = $this->managerRegistry
                        ->getRepository(TranslationInterface::class)
                        ->findDefault();
                }

                $this->executeNodeCreation($input->getArgument('node-name'), $type, $translation);
            } else {
                $this->io->error('"' . $typeName . '" node type does not exist.');
                return 1;
            }
            return 0;
        } else {
            $this->io->error($existingNode->getNodeName() . ' node already exists.');
            return 1;
        }
    }

    /**
     * @param string $nodeName
     * @param NodeType $type
     * @param TranslationInterface $translation
     */
    private function executeNodeCreation(
        string $nodeName,
        NodeType $type,
        TranslationInterface $translation
    ): void {
        $node = $this->nodeFactory->create($nodeName, $type, $translation);
        $source = $node->getNodeSources()->first() ?: null;
        if (null === $source) {
            throw new \InvalidArgumentException('Node source is null');
        }
        $fields = $type->getFields();

        foreach ($fields as $field) {
            if (!$field->isVirtual()) {
                $question = new Question('<question>[Field ' . $field->getLabel() . ']</question> : ', null);
                $fValue = $this->io->askQuestion($question);
                $setterName = $field->getSetterName();
                $source->$setterName($fValue);
            }
        }

        $this->managerRegistry->getManagerForClass(Node::class)->flush();
        $this->io->success('Node “' . $nodeName . '” created at root level.');
    }
}
