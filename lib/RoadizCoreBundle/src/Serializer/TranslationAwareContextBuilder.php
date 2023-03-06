<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer;

use ApiPlatform\Core\Serializer\SerializerContextBuilderInterface;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use RZ\Roadiz\CoreBundle\Repository\TranslationRepository;
use Symfony\Component\HttpFoundation\Request;

final class TranslationAwareContextBuilder implements SerializerContextBuilderInterface
{
    private ManagerRegistry $managerRegistry;
    private SerializerContextBuilderInterface $decorated;
    private PreviewResolverInterface $previewResolver;

    public function __construct(
        SerializerContextBuilderInterface $decorated,
        ManagerRegistry $managerRegistry,
        PreviewResolverInterface $previewResolver
    ) {
        $this->decorated = $decorated;
        $this->managerRegistry = $managerRegistry;
        $this->previewResolver = $previewResolver;
    }
    /**
     * @inheritDoc
     */
    public function createFromRequest(Request $request, bool $normalization, array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);

        if (!isset($context['translation']) || !($context['translation'] instanceof TranslationInterface)) {
            /** @var TranslationRepository $repository */
            $repository = $this->managerRegistry
                ->getRepository(TranslationInterface::class);

            if ($this->previewResolver->isPreview()) {
                $translation = $repository->findOneByLocaleOrOverrideLocale(
                    $request->query->get('_locale', $request->getLocale())
                );
            } else {
                $translation = $repository->findOneAvailableByLocaleOrOverrideLocale(
                    $request->query->get('_locale', $request->getLocale())
                );
            }

            if ($translation instanceof TranslationInterface) {
                $context['translation'] = $translation;
            }
        }
        return $context;
    }
}
