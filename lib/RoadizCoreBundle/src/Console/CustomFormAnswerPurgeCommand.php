<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Entity\CustomForm;
use RZ\Roadiz\CoreBundle\Entity\CustomFormAnswer;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\Documents\Events\DocumentDeletedEvent;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class CustomFormAnswerPurgeCommand extends Command
{
    private ManagerRegistry $managerRegistry;
    private EventDispatcherInterface $eventDispatcher;
    private LoggerInterface $logger;

    public function __construct(
        ManagerRegistry $managerRegistry,
        EventDispatcherInterface $eventDispatcher,
        LoggerInterface $logger,
        string $name = null
    ) {
        parent::__construct($name);
        $this->managerRegistry = $managerRegistry;
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;
    }

    protected function configure(): void
    {
        $this->setName('custom-form-answer:prune')
            ->setDescription('Prune all custom-form answers older than custom-form retention time policy.')
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $customForms = $this->managerRegistry
            ->getRepository(CustomForm::class)
            ->findAllWithRetentionTime();

        foreach ($customForms as $customForm) {
            if (null !== $interval = $customForm->getRetentionTimeInterval()) {
                $purgeBefore = (new \DateTime())->sub($interval);
                $customFormAnswers = $this->managerRegistry
                    ->getRepository(CustomFormAnswer::class)
                    ->findByCustomFormSubmittedBefore($customForm, $purgeBefore);
                $count = count($customFormAnswers);

                $documents = $this->managerRegistry
                    ->getRepository(Document::class)
                    ->findByCustomFormSubmittedBefore($customForm, $purgeBefore);
                $documentsCount = count($documents);

                if ($output->isVeryVerbose()) {
                    $io->info(\sprintf(
                        'Checking if “%s” custom-form has answers before %s',
                        $customForm->getName(),
                        $purgeBefore->format('Y-m-d H:i')
                    ));
                }

                if ($count > 0) {
                    $io->info(\sprintf(
                        'Purge %d custom-form answer(s) with %d documents(s) from “%s” before %s',
                        $count,
                        $documentsCount,
                        $customForm->getName(),
                        $purgeBefore->format('Y-m-d H:i')
                    ));

                    if (
                        !$input->getOption('dry-run') &&
                        (!$input->isInteractive() || $io->confirm(\sprintf(
                            'Are you sure you want to delete %d custom-form answer(s) and %d document(s) from “%s” before %s',
                            $count,
                            $documentsCount,
                            $customForm->getName(),
                            $purgeBefore->format('Y-m-d H:i')
                        ), false))
                    ) {
                        $this->managerRegistry
                            ->getRepository(CustomFormAnswer::class)
                            ->deleteByCustomFormSubmittedBefore($customForm, $purgeBefore);

                        foreach ($documents as $document) {
                            $this->eventDispatcher->dispatch(
                                new DocumentDeletedEvent($document)
                            );
                            if ($output->isVeryVerbose()) {
                                $io->info(\sprintf(
                                    '“%s” document has been deleted',
                                    $document->getRelativePath()
                                ));
                            }
                            $this->managerRegistry->getManager()->remove($document);
                        }
                        $this->managerRegistry->getManager()->flush();
                        $this->logger->info(\sprintf(
                            '%d answer(s) and %d document(s) were deleted from “%s” custom-form before %s',
                            $count,
                            $documentsCount,
                            $customForm->getName(),
                            $purgeBefore->format('Y-m-d H:i')
                        ));
                    }
                }
            }
        }

        return 0;
    }
}
