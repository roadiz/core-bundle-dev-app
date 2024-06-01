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
use RZ\Roadiz\CoreBundle\Mailer\EmailManager;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Mime\Address;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[AsMessageHandler]
final class CustomFormAnswerNotifyMessageHandler
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly EmailManager $emailManager,
        private readonly Settings $settingsBag,
        private readonly FilesystemOperator $documentsStorage,
        private readonly LoggerInterface $logger,
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
            ["name" => "ip.address", "value" => $answer->getIp()],
            ["name" => "submittedAt", "value" => $answer->getSubmittedAt()->format('Y-m-d H:i:s')],
        ];
        $emailFields = array_merge(
            $emailFields,
            $answer->toArray(false)
        );

        $receiver = array_filter(
            array_map('trim', explode(',', $answer->getCustomForm()->getEmail() ?? ''))
        );
        $receiver = array_map(function (string $email) {
            return new Address($email);
        }, $receiver);
        $this->sendAnswer(
            $answer,
            [
                'mailContact' => $message->getSenderAddress(),
                'fields' => $emailFields,
                'customForm' => $answer->getCustomForm(),
                'title' => $message->getTitle(),
                'requestLocale' => $message->getLocale(),
            ],
            $receiver
        );
    }

    /**
     * Send an answer form by Email.
     *
     * @param CustomFormAnswer $answer
     * @param array $assignation
     * @param string|array|null $receiver
     * @throws TransportExceptionInterface
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    private function sendAnswer(
        CustomFormAnswer $answer,
        array $assignation,
        $receiver
    ): void {
        $defaultSender = $this->settingsBag->get('email_sender');
        $defaultSender = !empty($defaultSender) ? $defaultSender : 'sender@roadiz.io';
        $this->emailManager->setAssignation($assignation);
        $this->emailManager->setEmailTemplate('@RoadizCore/email/forms/answerForm.html.twig');
        $this->emailManager->setEmailPlainTextTemplate('@RoadizCore/email/forms/answerForm.txt.twig');
        $this->emailManager->setSubject($assignation['title']);
        $this->emailManager->setEmailTitle($assignation['title']);
        $this->emailManager->setSender($defaultSender);

        try {
            foreach ($answer->getAnswerFields() as $customFormAnswerAttr) {
                /** @var DocumentInterface $document */
                foreach ($customFormAnswerAttr->getDocuments() as $document) {
                    $this->emailManager->addResource(
                        $this->documentsStorage->readStream($document->getMountPath()),
                        $document->getFilename(),
                        $this->documentsStorage->mimeType($document->getMountPath())
                    );
                }
            }
        } catch (FilesystemException $exception) {
            $this->logger->error($exception->getMessage(), [
                'entity' => $answer
            ]);
        }

        if (empty($receiver)) {
            $this->emailManager->setReceiver($defaultSender);
        } else {
            $this->emailManager->setReceiver($receiver);
        }

        // Send the message
        $this->emailManager->send();
    }
}
