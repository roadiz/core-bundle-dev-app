<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model;
use ApiPlatform\OpenApi\OpenApi;

final readonly class JwtDecorator implements OpenApiFactoryInterface
{
    public function __construct(
        private OpenApiFactoryInterface $decorated,
    ) {
    }

    #[\Override]
    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);

        $openApi = $openApi->withComponents(
            $openApi->getComponents()
                ->withSecuritySchemes(new \ArrayObject([
                    ...($openApi->getComponents()->getSecuritySchemes()?->getArrayCopy() ?? []),
                    'JWT' => new Model\SecurityScheme(
                        type: 'http',
                        scheme: 'bearer',
                        bearerFormat: 'JWT'
                    ),
                ]))
                ->withSchemas(new \ArrayObject([
                    ...($openApi->getComponents()->getSchemas()?->getArrayCopy() ?? []),
                    'TokenResponse' => new \ArrayObject([
                        'type' => 'object',
                        'properties' => [
                            'token' => [
                                'type' => 'string',
                                'readOnly' => true,
                            ],
                        ],
                    ]),
                    'InvalidCredentialsResponse' => new \ArrayObject([
                        'type' => 'object',
                        'properties' => [
                            'code' => [
                                'type' => 'string',
                                'readOnly' => true,
                                'example' => '401',
                            ],
                            'message' => [
                                'type' => 'string',
                                'readOnly' => true,
                                'example' => 'Invalid credentials',
                            ],
                        ],
                    ]),
                    'CredentialsRequest' => new \ArrayObject([
                        'type' => 'object',
                        'properties' => [
                            'username' => [
                                'type' => 'string',
                                'example' => 'johndoe@example.com',
                            ],
                            'password' => [
                                'type' => 'string',
                                'example' => 'apassword',
                            ],
                        ],
                    ]),
                ]))
        );

        $pathItem = new Model\PathItem(
            ref: 'JWT Token',
            post: new Model\Operation(
                operationId: 'postCredentialsItem',
                tags: ['Authentication'],
                responses: [
                    '401' => new Model\Response(
                        description: 'Invalid credentials',
                        content: new \ArrayObject([
                            'application/json' => new Model\MediaType(schema: new \ArrayObject(['$ref' => '#/components/schemas/InvalidCredentialsResponse'])),
                        ])
                    ),
                    '200' => new Model\Response(
                        description: 'JWT token response',
                        content: new \ArrayObject([
                            'application/json' => new Model\MediaType(schema: new \ArrayObject(['$ref' => '#/components/schemas/TokenResponse'])),
                        ])
                    ),
                ],
                summary: 'Get JWT token to login.',
                requestBody: new Model\RequestBody(
                    description: 'Generate new JWT Token',
                    content: new \ArrayObject([
                        'application/json' => new Model\MediaType(schema: new \ArrayObject(['$ref' => '#/components/schemas/CredentialsRequest'])),
                    ]),
                ),
                security: [],
            ),
        );

        /*
         * Make sure OpenApi path is the same as API firewall json login:
         * security.firewalls.api.json_login.check_path
         */
        $openApi->getPaths()->addPath('/api/token', $pathItem);

        return $openApi;
    }
}
