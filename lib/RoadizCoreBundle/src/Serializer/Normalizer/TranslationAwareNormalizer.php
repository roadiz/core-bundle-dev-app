<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\Normalizer;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Api\Model\WebResponseInterface;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use RZ\Roadiz\CoreBundle\Repository\TranslationRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class TranslationAwareNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const string ALREADY_CALLED = 'TRANSLATION_AWARE_NORMALIZER_ALREADY_CALLED';

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ManagerRegistry $managerRegistry,
        private readonly PreviewResolverInterface $previewResolver,
    ) {
    }

    #[\Override]
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        if ($object instanceof WebResponseInterface) {
            $item = $object->getItem();
            if ($item instanceof NodesSources) {
                $context['translation'] = $item->getTranslation();
            } elseif (method_exists($item, 'getLocale') && is_string($item->getLocale())) {
                $context['translation'] = $this->getTranslationFromLocale($item->getLocale());
            }
        } elseif ($object instanceof NodesSources) {
            $context['translation'] = $object->getTranslation();
        } elseif (!isset($context['translation']) || !($context['translation'] instanceof TranslationInterface)) {
            $translation = $this->getTranslationFromRequest();
            if (null !== $translation) {
                $context['translation'] = $translation;
            }
        }

        $context[self::ALREADY_CALLED] = true;

        return $this->normalizer->normalize($object, $format, $context);
    }

    private function getTranslationFromLocale(string $locale): ?TranslationInterface
    {
        /** @var TranslationRepository $repository */
        $repository = $this->managerRegistry
            ->getRepository(TranslationInterface::class);

        if ($this->previewResolver->isPreview()) {
            return $repository->findOneByLocaleOrOverrideLocale($locale);
        } else {
            return $repository->findOneAvailableByLocaleOrOverrideLocale($locale);
        }
    }

    private function getTranslationFromRequest(): ?TranslationInterface
    {
        $request = $this->requestStack->getMainRequest();

        if (null === $request) {
            return $this->managerRegistry
                ->getRepository(Translation::class)
                ->findDefault();
        }

        /*
         * Try to get translation resolved from LocaleSubscriber before
         */
        $requestTranslation = $request->attributes->get('_translation');
        if ($requestTranslation instanceof TranslationInterface) {
            return $requestTranslation;
        }

        $locale = $request->query->get('_locale', $request->getLocale());
        if (
            \is_string($locale)
            && null !== $translation = $this->getTranslationFromLocale($locale)
        ) {
            return $translation;
        }

        return null;
    }

    #[\Override]
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        // Make sure we're not called twice
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return true;
    }

    #[\Override]
    public function getSupportedTypes(?string $format): array
    {
        return [
            '*' => false,
        ];
    }
}
