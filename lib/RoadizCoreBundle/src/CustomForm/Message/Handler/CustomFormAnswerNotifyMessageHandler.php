<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\CustomForm\Message\Handler;

use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\CustomForm\Message\CustomFormAnswerNotifyMessage;
use RZ\Roadiz\CoreBundle\Entity\CustomFormAnswer;
use RZ\Roadiz\CoreBundle\Notifier\CustomFormAnswerNotification;
use RZ\Roadiz\CoreBundle\Repository\CustomFormAnswerRepository;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Notifier\Recipient\RecipientInterface;

#[AsMessageHandler]
final readonly class CustomFormAnswerNotifyMessageHandler
{
    public function __construct(
        private CustomFormAnswerRepository $customFormAnswerRepository,
        private FilesystemOperator $documentsStorage,
        private NotifierInterface $notifier,
        private LoggerInterface $messengerLogger,
        private bool $useReplyTo = true,
    ) {
    }

    public function __invoke(CustomFormAnswerNotifyMessage $message): void
    {
        $answer = $this->customFormAnswerRepository->find($message->getCustomFormAnswerId());

        if (!($answer instanceof CustomFormAnswer)) {
            throw new UnrecoverableMessageHandlingException('CustomFormAnswer not found');
        }

        $receivers = $this->getCustomFormReceivers($answer);

        if (empty($receivers)) {
            return;
        }

        $emailFields = [
            ['name' => 'ip.address', 'value' => $answer->getIp()],
            ['name' => 'submittedAt', 'value' => $answer->getSubmittedAt()->format('Y-m-d H:i:s')],
        ];
        $emailFields = array_merge(
            $emailFields,
            $answer->toArray(false)
        );

        $answerSenderEmail = $answer->getEmail();

        $notification = new CustomFormAnswerNotification(
            [
                'fields' => $emailFields,
                'customForm' => $answer->getCustomForm(),
                'title' => $message->getTitle(),
                'requestLocale' => $message->getLocale(),
            ],
            locale: $message->getLocale(),
            resources: $this->getAnswerDataParts($answer),
            replyTo: $answerSenderEmail && $this->useReplyTo ? new Address($answerSenderEmail) : null,
            subject: $message->getTitle(),
        );
        $this->notifier->send($notification, ...$receivers);
    }

    /**
     * @return RecipientInterface[]
     */
    private function getCustomFormReceivers(CustomFormAnswer $answer): array
    {
        $receiver = array_filter(
            array_map('trim', explode(',', $answer->getCustomForm()->getEmail() ?? ''))
        );

        return array_map(fn (string $email) => new Recipient(email: $email), $receiver);
    }

    /**
     * @return array<DataPart>
     */
    private function getAnswerDataParts(CustomFormAnswer $answer): array
    {
        $resources = [];
        try {
            foreach ($answer->getAnswerFields() as $customFormAnswerAttr) {
                /** @var DocumentInterface $document */
                foreach ($customFormAnswerAttr->getDocuments() as $document) {
                    $resources[] = new DataPart(
                        $this->documentsStorage->readStream($document->getMountPath()),
                        $document->getFilename(),
                        $this->documentsStorage->mimeType($document->getMountPath())
                    );
                    $this->messengerLogger->debug(sprintf(
                        'Joining document %s to email.',
                        $document->getFilename()
                    ));
                }
            }
        } catch (FilesystemException $exception) {
            $this->messengerLogger->error($exception->getMessage(), [
                'entity' => $answer,
            ]);
        }

        return $resources;
    }
}
