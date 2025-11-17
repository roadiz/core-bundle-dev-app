<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Ajax;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Extends common back-office controller, but add a request validation
 * to secure Ajax connexions.
 */
abstract class AbstractAjaxController extends AbstractController
{
    public const string AJAX_TOKEN_INTENTION = 'rozier_ajax';

    public function __construct(
        protected readonly ManagerRegistry $managerRegistry,
        protected readonly SerializerInterface $serializer,
        protected readonly TranslatorInterface $translator,
    ) {
    }

    protected static array $validMethods = [
        Request::METHOD_POST,
        Request::METHOD_GET,
    ];

    protected function getTranslation(Request $request): ?TranslationInterface
    {
        $translationId = $request->get('translationId', null);
        if (\is_numeric($translationId) && $translationId > 0) {
            $translation = $this->managerRegistry
                ->getRepository(Translation::class)
                ->find($translationId);
            if (null !== $translation) {
                return $translation;
            }
        }

        return $this->managerRegistry->getRepository(Translation::class)->findDefault();
    }

    /**
     * @return bool Return true if request is valid, else throw exception
     *
     * @deprecated Use AbstractAjaxController::validateCsrfToken and DTO
     */
    protected function validateRequest(Request $request, string $method = 'POST', bool $requestCsrfToken = true): bool
    {
        if (empty($request->get('_action'))) {
            throw new BadRequestHttpException('Wrong action requested');
        }

        if (true === $requestCsrfToken) {
            $this->validateCsrfToken($request->get('_token'));
        }

        if (
            in_array(\mb_strtolower($method), static::$validMethods)
            && \mb_strtolower($request->getMethod()) != \mb_strtolower($method)
        ) {
            throw new BadRequestHttpException('Bad method');
        }

        return true;
    }

    protected function validateCsrfToken(?string $csrfToken): void
    {
        if (!$this->isCsrfTokenValid(static::AJAX_TOKEN_INTENTION, $csrfToken)) {
            throw new BadRequestHttpException('Bad CSRF token');
        }
    }

    protected function sortIsh(array &$arr, array $map): array
    {
        $return = [];

        while ($element = array_shift($map)) {
            foreach ($arr as $key => $value) {
                if ($element == $value->getId()) {
                    $return[] = $value;
                    unset($arr[$key]);
                    break;
                }
            }
        }

        return $return;
    }

    protected function createSerializedResponse(array $data): JsonResponse
    {
        return new JsonResponse(
            $this->serializer->serialize(
                $data,
                'json',
                ['groups' => [
                    'document_display',
                    'explorer_thumbnail',
                    'node_type:display',
                    'model',
                ]]
            ),
            200,
            [],
            true
        );
    }
}
