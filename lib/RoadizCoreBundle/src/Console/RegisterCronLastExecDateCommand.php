<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Setting;
use RZ\Roadiz\CoreBundle\Repository\SettingRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'cron:set-last-exec-date',
    description: 'Persist last execution date of cron job into database.',
)]
final class RegisterCronLastExecDateCommand extends Command
{
    public function __construct(
        private readonly SettingRepository $settingRepository,
        private readonly ManagerRegistry $managerRegistry,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $manager = $this->managerRegistry->getManager();
        $parameter = $this->settingRepository->findOneByName('cron_last_exec_date');
        if (null === $parameter) {
            $parameter = new Setting();
            $parameter->setName('cron_last_exec_date');
            $manager->persist($parameter);
        }

        $parameter->setValue(new \DateTimeImmutable());
        $manager->flush();
        $io->success('Last execution date of cron job has been persisted.');

        return Command::SUCCESS;
    }
}
