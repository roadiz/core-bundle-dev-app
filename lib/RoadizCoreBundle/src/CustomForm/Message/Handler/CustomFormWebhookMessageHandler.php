<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\CustomForm\Message\Handler;

use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\CustomForm\Message\CustomFormWebhookMessage;
use RZ\Roadiz\CoreBundle\CustomForm\Webhook\CustomFormWebhookProviderRegistry;
use RZ\Roadiz\CoreBundle\Entity\CustomFormAnswer;
use RZ\Roadiz\CoreBundle\Repository\CustomFormAnswerRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\RecoverableMessageHandlingException;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

/**
 * Handler for CustomFormWebhookMessage.
 * Sends the webhook to the configured provider.
 */
#[AsMessageHandler]
final readonly class CustomFormWebhookMessageHandler
{
    public function __construct(
        private CustomFormAnswerRepository $customFormAnswerRepository,
        private CustomFormWebhookProviderRegistry $providerRegistry,
        private LoggerInterface $messengerLogger,
    ) {
    }

    public function __invoke(CustomFormWebhookMessage $message): void
    {
        $answer = $this->customFormAnswerRepository->find($message->getCustomFormAnswerId());

        if (!$answer instanceof CustomFormAnswer) {
            throw new UnrecoverableMessageHandlingException(sprintf('CustomFormAnswer #%d not found', $message->getCustomFormAnswerId()));
        }

        $provider = $this->providerRegistry->getProvider($message->getProviderName());

        if (!$provider) {
            throw new UnrecoverableMessageHandlingException(sprintf('Webhook provider "%s" not found', $message->getProviderName()));
        }

        if (!$provider->isConfigured()) {
            throw new UnrecoverableMessageHandlingException(sprintf('Webhook provider "%s" is not properly configured', $message->getProviderName()));
        }

        try {
            $success = $provider->sendWebhook(
                $answer,
                $message->getFieldMapping(),
                $message->getExtraConfig()
            );

            if (!$success) {
                throw new RecoverableMessageHandlingException(sprintf('Webhook provider "%s" returned false', $message->getProviderName()));
            }

            $this->messengerLogger->info('CustomForm webhook sent successfully', [
                'answer_id' => $message->getCustomFormAnswerId(),
                'provider' => $message->getProviderName(),
            ]);
        } catch (UnrecoverableMessageHandlingException $e) {
            throw $e;
        } catch (\Throwable $e) {
            // Log the error and throw a recoverable exception so the message can be retried
            $this->messengerLogger->error('Failed to send CustomForm webhook', [
                'answer_id' => $message->getCustomFormAnswerId(),
                'provider' => $message->getProviderName(),
                'error' => $e->getMessage(),
            ]);

            throw new RecoverableMessageHandlingException(sprintf('Failed to send webhook: %s', $e->getMessage()), 0, $e);
        }
    }
}
