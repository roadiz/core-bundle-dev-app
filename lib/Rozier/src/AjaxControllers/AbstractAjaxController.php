<?php

declare(strict_types=1);

namespace Themes\Rozier\AjaxControllers;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Themes\Rozier\RozierApp;

/**
 * Extends common back-office controller, but add a request validation
 * to secure Ajax connexions.
 */
abstract class AbstractAjaxController extends RozierApp
{
    public function __construct(
        protected readonly SerializerInterface $serializer
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
            $translation = $this->em()->find(
                Translation::class,
                $translationId
            );
            if (null !== $translation) {
                return $translation;
            }
        }

        return $this->em()->getRepository(Translation::class)->findDefault();
    }

    /**
     * @param Request $request
     * @param string  $method
     * @param bool    $requestCsrfToken
     *
     * @return bool  Return true if request is valid, else throw exception
     */
    protected function validateRequest(Request $request, string $method = 'POST', bool $requestCsrfToken = true): bool
    {
        if (empty($request->get('_action'))) {
            throw new BadRequestHttpException('Wrong action requested');
        }

        if (
            $requestCsrfToken === true &&
            !$this->isCsrfTokenValid(static::AJAX_TOKEN_INTENTION, $request->get('_token'))
        ) {
            throw new BadRequestHttpException('Bad CSRF token');
        }

        if (
            in_array(\mb_strtolower($method), static::$validMethods) &&
            \mb_strtolower($request->getMethod()) != \mb_strtolower($method)
        ) {
            throw new BadRequestHttpException('Bad method');
        }

        return true;
    }

    protected function sortIsh(array &$arr, array $map): array
    {
        $return = [];

        while ($element = array_shift($map)) {
            foreach ($arr as $key => $value) {
                if ($element == $value->getId()) {
                    $return[] = $value;
                    unset($arr[$key]);
                    break 1;
                }
            }
        }

        return $return;
    }

    /**
     * @param array $data
     * @return JsonResponse
     */
    protected function createSerializedResponse(array $data): JsonResponse
    {
        return new JsonResponse(
            $this->serializer->serialize(
                $data,
                'json',
                SerializationContext::create()->setGroups([
                    'document_display',
                    'explorer_thumbnail',
                    'model'
                ])
            ),
            200,
            [],
            true
        );
    }
}
