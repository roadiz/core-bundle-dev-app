<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Controller;

use ApiPlatform\Metadata\IriConverterInterface;
use RZ\Roadiz\CoreBundle\Api\DataTransformer\WebResponseDataTransformerInterface;
use RZ\Roadiz\CoreBundle\Api\Model\WebResponseInterface;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
final class DefaultNodeSourceController extends AbstractController
{
    public function __construct(
        private readonly WebResponseDataTransformerInterface $webResponseDataTransformer,
        private readonly IriConverterInterface $iriConverter,
    ) {
    }

    public function __invoke(Request $request, NodesSources $nodeSource): Response
    {
        $request->attributes->set('_translation', $nodeSource->getTranslation());
        $request->attributes->set('_locale', $nodeSource->getTranslation()->getPreferredLocale());
        $iri = $this->iriConverter->getIriFromResource($nodeSource);
        if (null !== $iri) {
            $request->attributes->set('_resources', $request->attributes->get('_resources', []) + [$iri => $iri]);
        }

        $data = $this->webResponseDataTransformer->transform($nodeSource, WebResponseInterface::class);

        return $this->render('@RoadizCore/nodeSource/default.html.twig', [
            'webResponse' => $data,
        ]);
    }
}
