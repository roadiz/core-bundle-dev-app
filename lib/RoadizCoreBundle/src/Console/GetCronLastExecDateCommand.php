<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use RZ\Roadiz\CoreBundle\Entity\Setting;
use RZ\Roadiz\CoreBundle\Repository\SettingRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'cron:get-last-exec-date',
    description: 'Fetch last execution date of cron job into database.',
)]
final class GetCronLastExecDateCommand extends Command
{
    public function __construct(
        private readonly SettingRepository $settingRepository,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $setting = $this->settingRepository->findOneByName('cron_last_exec_date');
        if (!$setting instanceof Setting) {
            $io->warning('Last execution date of cron job has not been persisted yet.');

            return Command::FAILURE;
        }

        $io->success(sprintf(
            'Last execution date of cron job is %s.',
            $setting->getRawValue()
        ));

        return Command::SUCCESS;
    }
}
