<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model;
use ApiPlatform\OpenApi\OpenApi;

final readonly class WebResponseDecorator implements OpenApiFactoryInterface
{
    public function __construct(
        private OpenApiFactoryInterface $decorated,
    ) {
    }

    #[\Override]
    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);
        $pathItem = $openApi->getPaths()->getPath('/api/web_response_by_path');
        if (null === $pathItem) {
            return $openApi;
        }

        $operation = $pathItem->getGet();

        if (null === $operation) {
            return $openApi;
        }

        $openApi->getPaths()->addPath('/api/web_response_by_path', $pathItem->withGet(
            $operation->withParameters([
                // override completely parameters
                new Model\Parameter(
                    'path',
                    'query',
                    'Resource path, or `/` for home page',
                    true,
                ),
                (new Model\Parameter(
                    '_preview',
                    'query',
                    'Enables preview mode (requires a valid bearer JWT token)',
                    false
                ))->withSchema(['type' => 'boolean'])->withExample('1'),
            ])
        ));

        return $openApi;
    }
}
