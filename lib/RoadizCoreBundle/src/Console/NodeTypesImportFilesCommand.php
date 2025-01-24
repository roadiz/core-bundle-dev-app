<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use RZ\Roadiz\CoreBundle\Repository\NodeTypeRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class NodeTypesImportFilesCommand extends Command
{
    public function __construct(
        private readonly NodeTypeRepositoryInterface $repository,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('nodetypes:import-files')
            ->setDescription('Import from config all node-type YAML files.')
            ->addArgument('file', InputArgument::OPTIONAL, 'Only file to import')
        ;
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($onlyFile = $input->getArgument('file')) {
            $nodeTypes = $this->repository->findOneByName($onlyFile);
        } else {
            $nodeTypes = $this->repository->findAll();
        }
        if (empty($nodeTypes)) {
            return 0;
        }

        return 1;
    }
}
