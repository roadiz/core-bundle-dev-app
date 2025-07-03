<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use RZ\Roadiz\CoreBundle\Notifier\BaseEmailNotification;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mime\Address;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;

final class MailerTestCommand extends Command
{
    public function __construct(
        private readonly NotifierInterface $notifier,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    #[\Override]
    protected function configure(): void
    {
        $this->setName('mailer:send:test')
            ->addArgument('email', InputArgument::REQUIRED, 'Receiver email address.')
            ->setDescription('Send a test email.')
        ;
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $title = '[test] Roadiz test email';
        $to = Address::create($input->getArgument('email'));

        $notification = (new BaseEmailNotification([
            'title' => $title,
            'content' => <<<MD
### {$title}

This is a test email send to *{$to->getAddress()}* from `mailer:send:test` CLI command.
MD,
        ], $title, ['email']));

        $this->notifier->send($notification, new Recipient($to->getAddress()));
        (new SymfonyStyle($input, $output))->success('Email sent.');

        return 0;
    }
}
