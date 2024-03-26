<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Property;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use RZ\Roadiz\CoreBundle\Api\Model\WebResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PropertyInfo\Type;

class WebResponseItemPropertyMetadataFactory implements PropertyMetadataFactoryInterface
{
    public function __construct(
        private readonly PropertyMetadataFactoryInterface $decorated,
        private readonly RequestStack $requestStack
    ) {
    }

    public function create(string $resourceClass, string $property, array $options = []): ApiProperty
    {
        $apiProperty = $this->decorated->create($resourceClass, $property, $options);
        $request = $this->requestStack->getMainRequest();

        if (null !== $request &&
            ($webResponseClass = $request->attributes->get('_web_response_item_class')) &&
            $resourceClass === WebResponse::class &&
            $property === 'item'
        ) {
            if (\is_string($webResponseClass)) {
                return $apiProperty
                    ->withWritable(false)
                    ->withReadableLink(true)
                    ->withReadable(true)
                    ->withBuiltinTypes([
                        new Type('object', true, $webResponseClass)
                    ])
                ;
            }
        }

        return $apiProperty;
    }
}
