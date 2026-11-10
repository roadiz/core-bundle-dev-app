<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\Enricher;

use RZ\Roadiz\CoreBundle\Entity\CustomForm;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * Override CustomForm default normalization.
 */
#[AsTaggedItem(priority: 15)]
final readonly class CustomFormEnricher implements NormalizationEnricherInterface
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    #[\Override]
    public function supports(object $data, ?string $format, array $context): bool
    {
        return $data instanceof CustomForm;
    }

    /**
     * @param CustomForm $data
     */
    #[\Override]
    public function enrich(array $normalized, object $data, ?string $format, array $context): array
    {
        $normalized['name'] = $data->getDisplayName();
        $normalized['color'] = $data->getColor();
        $normalized['description'] = $data->getDescription();
        $normalized['slug'] = (new AsciiSlugger())->slug($data->getName())->snake()->toString();
        $normalized['open'] = $data->isFormStillOpen();

        if (
            isset($context['groups'])
            && \in_array('urls', $context['groups'], true)
        ) {
            $normalized['definitionUrl'] = $this->urlGenerator->generate('api_custom_forms_item_definition', [
                'id' => $data->getId(),
            ]);
            $normalized['postUrl'] = $this->urlGenerator->generate('api_custom_forms_item_post', [
                'id' => $data->getId(),
            ]);
        }

        return $normalized;
    }
}
