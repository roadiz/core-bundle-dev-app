<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class NodesDetailsCommand extends Command
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('nodes:show')
            ->setDescription('Show node details and data.')
            ->addArgument('nodeName', InputArgument::REQUIRED, 'Node name to show')
            ->addArgument('locale', InputArgument::REQUIRED, 'Translation locale to use')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $translation = $this->managerRegistry->getRepository(Translation::class)
                                           ->findOneBy(['locale' => $input->getArgument('locale')]);

        /**
         * @var NodesSources|null $source
         * @phpstan-ignore-next-line
         */
        $source = $this->managerRegistry->getRepository(NodesSources::class)
                                    ->setDisplayingNotPublishedNodes(true)
                                    ->findOneBy([
                                        'node.nodeName' => $input->getArgument('nodeName'),
                                        'translation' => $translation,
                                    ]);
        if (null !== $source) {
            $io->title(get_class($source));
            $io->title('Title');
            $io->text($source->getTitle());

            /** @var NodeTypeField $field */
            foreach ($source->getNode()->getNodeType()->getFields() as $field) {
                if (!$field->isVirtual()) {
                    $getter = $field->getGetterName();
                    $data = $source->$getter();

                    if (is_array($data)) {
                        $data = implode(', ', $data);
                    }
                    if ($data instanceof \DateTimeInterface) {
                        $data = $data->format('c');
                    }
                    if ($data instanceof \stdClass) {
                        $data = \json_encode($data);
                    }

                    if (!empty($data)) {
                        $io->title($field->getLabel());
                        $io->text($data);
                    }
                }
            }
        } else {
            $io->error('No node found.');
            return 1;
        }
        return 0;
    }
}
