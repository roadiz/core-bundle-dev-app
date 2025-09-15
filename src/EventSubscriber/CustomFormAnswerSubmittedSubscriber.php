<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use RZ\Roadiz\CoreBundle\Event\CustomFormAnswer\CustomFormAnswerSubmittedEvent;
use RZ\Roadiz\CoreBundle\Notifier\BaseEmailNotification;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;

final readonly class CustomFormAnswerSubmittedSubscriber implements EventSubscriberInterface
{
    public function __construct(private NotifierInterface $notifier)
    {
    }

    public function onCustomFormAnswerSubmittedEvent(CustomFormAnswerSubmittedEvent $event): void
    {
        $customFormAnswer = $event->getCustomFormAnswer();
        if (null === $email = $customFormAnswer->getEmail()) {
            return;
        }

        $to = Address::create($email);
        $title = $customFormAnswer->getCustomForm()->getName();

        $this->notifier->send(
            new BaseEmailNotification(
                [
                    'title' => '(TEST) Thanks for your submission: '.$customFormAnswer->getCustomForm()->getName(),
                    'content' => <<<MD
### {$title}

This is a test email send to *{$to->getAddress()}* after you submitted "{$title}" custom-form.
MD,
                ],
                '(TEST) Thanks for your submission: '.$customFormAnswer->getCustomForm()->getName(),
                ['email']
            ),
            new Recipient($to->getAddress())
        );
    }

    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            CustomFormAnswerSubmittedEvent::class => 'onCustomFormAnswerSubmittedEvent',
        ];
    }
}
