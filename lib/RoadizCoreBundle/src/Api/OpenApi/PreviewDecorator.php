<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\OpenApi;

final readonly class PreviewDecorator implements OpenApiFactoryInterface
{
    public function __construct(
        private OpenApiFactoryInterface $decorated,
    ) {
    }

    #[\Override]
    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);
        /** @var PathItem[] $paths */
        $paths = $openApi->getPaths()->getPaths();
        // For each GET path, add a new query parameter `_preview` to force preview mode
        foreach ($paths as $path => $pathItem) {
            $operation = $pathItem->getGet();
            if (null !== $operation) {
                $responses = $operation->getResponses();
                $responses['401'] = new Model\Response(
                    description: 'Invalid JWT Token'
                );

                $newOperation = $operation->withParameters([
                    ...($operation->getParameters() ?? []),
                    (new Model\Parameter(
                        '_preview',
                        'query',
                        'Enables preview mode (requires a valid bearer JWT token)',
                        false
                    ))->withSchema(['type' => 'boolean'])->withExample('1'),
                ])->withSecurity([
                    ...$operation->getSecurity() ?? [],
                    ['JWT' => []],
                ])->withResponses($responses);
                $openApi->getPaths()->addPath($path, $pathItem->withGet($newOperation));
            }
        }

        return $openApi;
    }
}
