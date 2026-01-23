<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Login;

use RZ\Roadiz\CoreBundle\Bag\Settings;
use RZ\Roadiz\Documents\MediaFinders\RandomImageFinder;
use RZ\Roadiz\Documents\UrlGenerators\DocumentUrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class LoginImageController extends AbstractController
{
    public function __construct(
        private readonly DocumentUrlGeneratorInterface $documentUrlGenerator,
        private readonly RandomImageFinder $randomImageFinder,
        private readonly Settings $settingsBag,
    ) {
    }

    #[Route(
        path: '/css/login/image',
        name: 'loginImagePage',
        methods: ['GET'],
    )]
    public function imageAction(Request $request): Response
    {
        $response = new JsonResponse();
        $response->setPublic();
        $response->setMaxAge(600);

        if (null !== $document = $this->settingsBag->getDocument('login_image')) {
            if (
                !$document->isPrivate()
                && $document->isProcessable()
            ) {
                $this->documentUrlGenerator->setDocument($document);
                $this->documentUrlGenerator->setOptions([
                    'width' => 1920,
                    'height' => 1920,
                    'quality' => 80,
                    'sharpen' => 5,
                ]);

                return $response->setData([
                    'url' => $this->documentUrlGenerator->getUrl(),
                ]);
            }
        }

        $feed = $this->randomImageFinder->getRandomBySearch('road');
        $url = null;

        if (null !== $feed) {
            $url = $feed['url'] ?? $feed['urls']['regular'] ?? $feed['urls']['full'] ?? $feed['urls']['raw'] ?? null;
        }

        return $response->setData([
            'url' => $url ?? '/themes/Rozier/static/assets/img/default_login.jpg',
        ]);
    }
}
