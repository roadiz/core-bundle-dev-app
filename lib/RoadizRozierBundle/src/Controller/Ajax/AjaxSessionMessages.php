<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Ajax;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;

final class AjaxSessionMessages extends AbstractAjaxController
{
    public function getMessagesAction(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_BACKEND_USER');

        $responseArray = [
            'statusCode' => Response::HTTP_OK,
            'status' => 'success',
        ];

        if ($request->hasPreviousSession()) {
            $session = $request->getSession();
            if ($session instanceof FlashBagAwareSessionInterface) {
                $responseArray['messages'] = $session->getFlashBag()->all();
            }
        }

        return new JsonResponse(
            $responseArray
        );
    }
}
