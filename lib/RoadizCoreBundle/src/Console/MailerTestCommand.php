<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use RZ\Roadiz\CoreBundle\Mailer\EmailManagerFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mime\Address;

final class MailerTestCommand extends Command
{
    public function __construct(
        private readonly EmailManagerFactory $emailManagerFactory,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('mailer:send:test')
            ->addArgument('email', InputArgument::REQUIRED, 'Receiver email address.')
            ->addOption('from', 'f', InputOption::VALUE_REQUIRED, 'Sender envelop email address.')
            ->setDescription('Send a test email.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $title = '[test] Roadiz test email';
        $to = Address::create($input->getArgument('email'));
        $from = Address::create($input->getOption('from') ?? 'test@roadiz.io');

        $this->emailManagerFactory->create()
            ->setReceiver($to)
            ->setSender($from)
            // Uses email_sender customizable setting
            ->setSubject($title)
            ->setEmailPlainTextTemplate('@RoadizCore/email/base_email.txt.twig')
            ->setEmailTemplate('@RoadizCore/email/base_email.html.twig')
            ->setAssignation([
                'title' => $title,
                'content' => 'This is a test email send to *' . $to->getAddress() . '* from `mailer:send:test` CLI command.',
                'mailContact' => $from->getAddress()
            ])
            ->send();
        (new SymfonyStyle($input, $output))->success('Email sent.');
        return 0;
    }
}
