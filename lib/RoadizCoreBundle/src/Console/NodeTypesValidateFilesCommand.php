<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use RZ\Roadiz\Contracts\NodeType\NodeTypeClassLocatorInterface;
use RZ\Roadiz\CoreBundle\Repository\NodeTypeRepositoryInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'nodetypes:validate-files',
    description: 'Validate all node-type YAML files located in <info>config/node_types</info>.',
)]
final class NodeTypesValidateFilesCommand extends Command
{
    public function __construct(
        private readonly NodeTypeRepositoryInterface $repository,
        private readonly NodeTypeClassLocatorInterface $nodeTypeClassLocator,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    #[\Override]
    protected function configure(): void
    {
        $this->addArgument('file', InputArgument::OPTIONAL, 'Single file to validate');
        $this->addOption('ignore-missing', null, InputOption::VALUE_NONE, 'Ignore missing files');
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $nodeTypes = [];
        if ($onlyFile = $input->getArgument('file')) {
            $nodeTypes[] = $this->repository
                ->findOneByName($onlyFile) ?? throw new \RuntimeException('Node type not found: '.$onlyFile);
        } else {
            $nodeTypes = $this->repository->findAll();
        }

        if (!$input->getOption('ignore-missing')) {
            foreach ($nodeTypes as $nodeType) {
                $repositoryClassName = $this->nodeTypeClassLocator->getRepositoryFullQualifiedClassName($nodeType);
                if (!class_exists($repositoryClassName)) {
                    $io->error('Missing repository class: '.$repositoryClassName);

                    return Command::FAILURE;
                }
                $entityClassName = $this->nodeTypeClassLocator->getSourceEntityFullQualifiedClassName($nodeType);
                if (!class_exists($entityClassName)) {
                    $io->error('Missing entity class: '.$entityClassName);

                    return Command::FAILURE;
                }
            }
        }

        $io->success('All node-type files are valid.');

        return Command::SUCCESS;
    }
}
