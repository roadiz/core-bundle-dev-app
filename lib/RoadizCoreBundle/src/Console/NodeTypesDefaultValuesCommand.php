<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\AbstractEntities\AbstractField;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use RZ\Roadiz\EntityGenerator\Field\DefaultValuesResolverInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class NodeTypesDefaultValuesCommand extends Command
{
    public function __construct(
        private readonly DefaultValuesResolverInterface $defaultValuesResolver,
        private readonly ManagerRegistry $managerRegistry,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('nodetypes:default-values')
            ->setDescription('Get all default values for a field across all node-types.')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'Field name'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');

        if (empty($name)) {
            throw new \InvalidArgumentException('Field name must not be empty.');
        }
        $enumFields = $this->managerRegistry->getRepository(NodeTypeField::class)->findBy([
            'type' => AbstractField::ENUM_T,
        ]);
        $enumFieldsNames = array_unique(array_map(function (NodeTypeField $field) {
            return $field->getName();
        }, $enumFields));

        $oneField = $this->managerRegistry->getRepository(NodeTypeField::class)->findOneBy([
            'name' => $name,
        ]);

        if (!$oneField instanceof NodeTypeField) {
            throw new \InvalidArgumentException('Field name must be a valid field name.');
        }
        if (!$oneField->isEnum()) {
            throw new \InvalidArgumentException('Field name must be an enum field. Valid fields names are: '.implode(', ', $enumFieldsNames));
        }

        $defaultValues = $this->defaultValuesResolver->getDefaultValuesAmongAllFields($oneField);
        $maxDefaultValuesLength = $this->defaultValuesResolver->getMaxDefaultValuesLengthAmongAllFields($oneField);

        $io = new SymfonyStyle($input, $output);
        $io->horizontalTable(['Field name', 'Label', 'Description', 'Default values', 'Max length'], [
            [
                $oneField->getName(),
                $oneField->getLabel(),
                $oneField->getDescription(),
                implode(', ', array_unique($defaultValues)),
                $maxDefaultValuesLength,
            ],
        ]);

        return 0;
    }
}
