<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Entity\CustomForm;
use RZ\Roadiz\CoreBundle\Entity\CustomFormAnswer;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\Documents\Events\DocumentDeletedEvent;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Scheduler\Attribute\AsCronTask;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsCronTask(expression: '0 3 * * *', jitter: 120, arguments: '-n -q')]
#[AsCommand(
    name: 'custom-form-answer:prune',
    description: 'Prune all custom-form answers older than custom-form retention time policy.',
)]
final class CustomFormAnswerPurgeCommand extends Command
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly LoggerInterface $logger,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    #[\Override]
    protected function configure(): void
    {
        $this->addOption('dry-run', 'd', InputOption::VALUE_NONE);
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $customForms = $this->managerRegistry
            ->getRepository(CustomForm::class)
            ->findAllWithRetentionTime();

        foreach ($customForms as $customForm) {
            $this->purgeCustomFormAnswers($customForm, $input, $output);
        }

        return 0;
    }

    protected function purgeCustomFormAnswers(CustomForm $customForm, InputInterface $input, OutputInterface $output): void
    {
        $io = new SymfonyStyle($input, $output);

        if (null === $interval = $customForm->getRetentionTimeInterval()) {
            return;
        }

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

        if ($count <= 0) {
            return;
        }

        $io->info(\sprintf(
            'Purge %d custom-form answer(s) with %d documents(s) from “%s” before %s',
            $count,
            $documentsCount,
            $customForm->getName(),
            $purgeBefore->format('Y-m-d H:i')
        ));

        if (
            !$input->getOption('dry-run')
            && (!$input->isInteractive() || $io->confirm(\sprintf(
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
