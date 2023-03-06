<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers;

use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\Documents\MediaFinders\RandomImageFinder;
use RZ\Roadiz\Documents\UrlGenerators\DocumentUrlGeneratorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Themes\Rozier\RozierApp;

class LoginController extends RozierApp
{
    private DocumentUrlGeneratorInterface $documentUrlGenerator;
    private RandomImageFinder $randomImageFinder;

    /**
     * @param DocumentUrlGeneratorInterface $documentUrlGenerator
     * @param RandomImageFinder $randomImageFinder
     */
    public function __construct(
        DocumentUrlGeneratorInterface $documentUrlGenerator,
        RandomImageFinder $randomImageFinder
    ) {
        $this->documentUrlGenerator = $documentUrlGenerator;
        $this->randomImageFinder = $randomImageFinder;
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function imageAction(Request $request): Response
    {
        $response = new JsonResponse();
        if (null !== $document = $this->getSettingsBag()->getDocument('login_image')) {
            if ($document instanceof Document && $document->isProcessable()) {
                $this->documentUrlGenerator->setDocument($document);
                $this->documentUrlGenerator->setOptions([
                    'width' => 1920,
                    'height' => 1920,
                    'quality' => 80,
                    'sharpen' => 5,
                ]);
                $response->setData([
                    'url' => $this->documentUrlGenerator->getUrl()
                ]);
                return $this->makeResponseCachable($request, $response, 60, true);
            }
        }

        $feed = $this->randomImageFinder->getRandomBySearch('road');
        $url = null;

        if (null !== $feed) {
            $url = $feed['url'] ?? $feed['urls']['regular'] ?? $feed['urls']['full'] ?? $feed['urls']['raw'] ?? null;
        }
        $response->setData([
            'url' => '/themes/Rozier/static/assets/img/default_login.jpg'
        ]);
        return $this->makeResponseCachable($request, $response, 60, true);
    }
}
