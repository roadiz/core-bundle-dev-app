<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Translator;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

#[AsController]
final readonly class TranslateAssistantController
{
    public function __construct(
        private TranslateAssistantInterface $translateAssistant,
    ) {
    }

    public function translateAction(
        #[MapRequestPayload] TranslateAssistantDto $dto,
    ): JsonResponse {
        return new JsonResponse(
            $this->translateAssistant->translate($dto)
        );
    }

    public function rephraseAction(
        #[MapRequestPayload] TranslateAssistantDto $dto,
    ): JsonResponse {
        return new JsonResponse(
            $this->translateAssistant->rephrase($dto)
        );
    }
}
