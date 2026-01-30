<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\CustomForm\Webhook;

use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Entity\CustomFormAnswer;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Abstract base class for webhook providers.
 */
abstract readonly class AbstractCustomFormWebhookProvider implements CustomFormWebhookProviderInterface
{
    public function __construct(
        protected HttpClientInterface $httpClient,
        protected LoggerInterface $logger,
    ) {
    }

    #[\Override]
    abstract public function getName(): string;

    #[\Override]
    abstract public function getDisplayName(): string;

    #[\Override]
    abstract public function isConfigured(): bool;

    #[\Override]
    public function getConfigSchema(): array
    {
        return [];
    }

    /**
     * Transform CustomFormAnswer data using the field mapping.
     *
     * @param CustomFormAnswer      $answer       The form answer
     * @param array<string, string> $fieldMapping Map of CustomForm field names to provider field names
     *
     * @return array<string, mixed> Mapped data
     */
    protected function mapAnswerData(CustomFormAnswer $answer, array $fieldMapping): array
    {
        $answerData = $answer->toArray(true);
        $mappedData = [];

        foreach ($fieldMapping as $customFormField => $providerField) {
            if (!empty($providerField) && !empty($answerData[$customFormField])) {
                $mappedData[$providerField] = $answerData[$customFormField];
            }
        }

        // Add metadata
        $mappedData['_submitted_at'] = $answer->getSubmittedAt()?->format('c');
        $mappedData['_ip'] = $answer->getIp();
        $mappedData['_custom_form_answer_id'] = $answer->getId();

        return $mappedData;
    }

    /**
     * Log webhook success.
     */
    protected function logSuccess(CustomFormAnswer $answer, string $message = 'Webhook sent successfully'): void
    {
        $this->logger->info(sprintf(
            '[%s] %s for CustomFormAnswer #%d',
            $this->getName(),
            $message,
            $answer->getId()
        ));
    }

    /**
     * Log webhook error.
     */
    protected function logError(CustomFormAnswer $answer, string $message, ?\Throwable $exception = null): void
    {
        $context = [
            'provider' => $this->getName(),
            'answer_id' => $answer->getId(),
        ];

        if ($exception) {
            $context['exception'] = $exception;
        }

        $this->logger->error($message, $context);
    }
}
