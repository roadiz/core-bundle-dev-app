<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model;
use ApiPlatform\OpenApi\OpenApi;

final class WebResponseDecorator implements OpenApiFactoryInterface
{
    private OpenApiFactoryInterface $decorated;

    public function __construct(
        OpenApiFactoryInterface $decorated
    ) {
        $this->decorated = $decorated;
    }

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);
        $schemas = $openApi->getComponents()->getSchemas();
        $pathItem = $openApi->getPaths()->getPath('/api/web_response_by_path');
        $operation = $pathItem->getGet();

        $openApi->getPaths()->addPath('/api/web_response_by_path', $pathItem->withGet(
            $operation->withParameters([new Model\Parameter(
                'path',
                'query',
                'Resource path, or `/` for home page',
                true,
            )])
        ));

        return $openApi;
    }
}
