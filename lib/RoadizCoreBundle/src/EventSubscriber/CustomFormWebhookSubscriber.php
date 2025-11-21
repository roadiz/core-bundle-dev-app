<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EventSubscriber;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\CustomForm\Message\CustomFormWebhookMessage;
use RZ\Roadiz\CoreBundle\CustomForm\Webhook\CustomFormWebhookProviderRegistry;
use RZ\Roadiz\CoreBundle\Event\CustomFormAnswer\CustomFormAnswerSubmittedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Dispatches webhooks when CustomFormAnswers are submitted.
 */
final readonly class CustomFormWebhookSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private CustomFormWebhookProviderRegistry $providerRegistry,
        private MessageBusInterface $messageBus,
        private ManagerRegistry $managerRegistry,
        private LoggerInterface $logger,
    ) {
    }

    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            CustomFormAnswerSubmittedEvent::class => 'onCustomFormAnswerSubmitted',
        ];
    }

    public function onCustomFormAnswerSubmitted(CustomFormAnswerSubmittedEvent $event): void
    {
        $answer = $event->getCustomFormAnswer();
        $customForm = $answer->getCustomForm();

        // Check if webhooks are enabled for this custom form
        if (!$customForm->isWebhookEnabled()) {
            return;
        }

        $provider = $customForm->getWebhookProvider();
        if (!$provider) {
            $this->logger->warning('CustomForm webhook is enabled but no provider is configured', [
                'custom_form_id' => $customForm->getId(),
            ]);

            return;
        }

        // Check if the provider exists
        if (!$this->providerRegistry->hasProvider($provider)) {
            $this->logger->error('CustomForm webhook provider not found', [
                'custom_form_id' => $customForm->getId(),
                'provider' => $provider,
            ]);

            return;
        }

        // Get answer ID and ensure it's persisted
        $answerId = $answer->getId();
        if (!$answerId) {
            // Flush to ensure we have an ID for idempotency
            $this->managerRegistry->getManager()->flush();
            $answerId = $answer->getId();
        }

        if (!$answerId) {
            $this->logger->error('Cannot dispatch webhook: CustomFormAnswer has no ID');

            return;
        }

        // Dispatch webhook message for async processing
        $this->messageBus->dispatch(new CustomFormWebhookMessage(
            $answerId,
            $provider,
            $customForm->getWebhookFieldMapping() ?? [],
            $customForm->getWebhookExtraConfig() ?? []
        ));

        $this->logger->info('CustomForm webhook message dispatched', [
            'answer_id' => $answerId,
            'provider' => $provider,
        ]);
    }
}
