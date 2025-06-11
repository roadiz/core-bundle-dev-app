<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\CustomForm\Message\Handler;

use Doctrine\Persistence\ManagerRegistry;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Bag\Settings;
use RZ\Roadiz\CoreBundle\CustomForm\Message\CustomFormAnswerNotifyMessage;
use RZ\Roadiz\CoreBundle\Entity\CustomFormAnswer;
use RZ\Roadiz\CoreBundle\Mailer\EmailManagerFactory;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Mime\Address;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[AsMessageHandler]
final readonly class CustomFormAnswerNotifyMessageHandler
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
        private EmailManagerFactory $emailManagerFactory,
        private Settings $settingsBag,
        private FilesystemOperator $documentsStorage,
        private LoggerInterface $messengerLogger,
        private bool $useReplyTo = true,
    ) {
    }

    public function __invoke(CustomFormAnswerNotifyMessage $message): void
    {
        $answer = $this->managerRegistry
            ->getRepository(CustomFormAnswer::class)
            ->find($message->getCustomFormAnswerId());

        if (!($answer instanceof CustomFormAnswer)) {
            throw new UnrecoverableMessageHandlingException('CustomFormAnswer not found');
        }

        $emailFields = [
            ['name' => 'ip.address', 'value' => $answer->getIp()],
            ['name' => 'submittedAt', 'value' => $answer->getSubmittedAt()->format('Y-m-d H:i:s')],
        ];
        $emailFields = array_merge(
            $emailFields,
            $answer->toArray(false)
        );

        $this->sendAnswer(
            $answer,
            [
                'fields' => $emailFields,
                'customForm' => $answer->getCustomForm(),
                'title' => $message->getTitle(),
                'requestLocale' => $message->getLocale(),
            ]
        );
    }

    /**
     * @return Address[]
     */
    private function getCustomFormReceivers(CustomFormAnswer $answer): array
    {
        $receiver = array_filter(
            array_map('trim', explode(',', $answer->getCustomForm()->getEmail() ?? ''))
        );

        return array_map(fn (string $email) => new Address($email), $receiver);
    }

    /**
     * Send an answer form by Email.
     *
     * @throws TransportExceptionInterface
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    private function sendAnswer(
        CustomFormAnswer $answer,
        array $assignation,
    ): void {
        $defaultSender = $this->settingsBag->get('email_sender');
        $defaultSender = filter_var($defaultSender, FILTER_VALIDATE_EMAIL) ? $defaultSender : 'sender@roadiz.io';
        $receivers = $this->getCustomFormReceivers($answer);

        $emailManager = $this->emailManagerFactory->create();
        $emailManager->setAssignation([
            ...$assignation,
            // Mail contact is support or sender
            'mailContact' => $emailManager->getSupportEmailAddress(),
        ]);
        $emailManager->setEmailTemplate('@RoadizCore/email/forms/answerForm.html.twig');
        $emailManager->setEmailPlainTextTemplate('@RoadizCore/email/forms/answerForm.txt.twig');
        $emailManager->setSubject($assignation['title']);
        $emailManager->setEmailTitle($assignation['title']);
        $emailManager->appendWebsiteIcon();

        /*
         * Set real sender if email is valid to enable Reply-To
         */
        $realSender = filter_var($answer->getEmail(), FILTER_VALIDATE_EMAIL) ? $answer->getEmail() : $defaultSender;
        if ($this->useReplyTo) {
            $emailManager->setSender($realSender);
        }

        try {
            foreach ($answer->getAnswerFields() as $customFormAnswerAttr) {
                /** @var DocumentInterface $document */
                foreach ($customFormAnswerAttr->getDocuments() as $document) {
                    $emailManager->addResource(
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

        if (empty($receivers)) {
            $emailManager->setReceiver($defaultSender);
        } else {
            $emailManager->setReceiver($receivers);
        }

        // Send the message
        $emailManager->send();
        $this->messengerLogger->debug(sprintf(
            'CustomForm (%s) answer sent to %s',
            $answer->getCustomForm()->getName(),
            $realSender
        ));
    }
}
