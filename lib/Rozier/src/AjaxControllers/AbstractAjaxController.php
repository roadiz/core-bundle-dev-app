<?php

declare(strict_types=1);

namespace Themes\Rozier\AjaxControllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Themes\Rozier\RozierApp;

/**
 * Extends common back-office controller, but add a request validation
 * to secure Ajax connexions.
 */
abstract class AbstractAjaxController extends RozierApp
{
    protected static array $validMethods = [
        Request::METHOD_POST,
        Request::METHOD_GET,
    ];

    /**
     * @param Request $request
     * @param string  $method
     * @param bool    $requestCsrfToken
     *
     * @return bool  Return true if request is valid, else throw exception
     */
    protected function validateRequest(Request $request, $method = 'POST', $requestCsrfToken = true)
    {
        if ($request->get('_action') == "") {
            throw new BadRequestHttpException('Wrong action requested');
        }

        if ($requestCsrfToken === true) {
            if (!$this->isCsrfTokenValid(static::AJAX_TOKEN_INTENTION, $request->get('_token'))) {
                throw new BadRequestHttpException('Bad CSRF token');
            }
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
}
