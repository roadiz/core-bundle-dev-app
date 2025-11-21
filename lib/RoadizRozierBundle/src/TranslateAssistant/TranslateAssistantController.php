<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\TranslateAssistant;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final readonly class TranslateAssistantController
{
    public function __construct(
        private TranslateAssistantInterface $translateAssistant,
    ) {
    }

    #[Route(
        path: '/rz-admin/translate-assistant/translate',
        name: 'translateAssistantTranslate',
        methods: ['POST'],
    )]
    public function translateAction(
        #[MapRequestPayload] TranslateAssistantInput $dto,
    ): JsonResponse {
        return new JsonResponse(
            $this->translateAssistant->translate($dto)
        );
    }

    #[Route(
        path: '/rz-admin/translate-assistant/rephrase',
        name: 'translateAssistantRephrase',
        methods: ['POST'],
    )]
    public function rephraseAction(
        #[MapRequestPayload] TranslateAssistantInput $dto,
    ): JsonResponse {
        return new JsonResponse(
            $this->translateAssistant->rephrase($dto)
        );
    }
}
