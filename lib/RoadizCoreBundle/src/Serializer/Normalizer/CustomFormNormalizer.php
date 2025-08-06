<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\Normalizer;

use RZ\Roadiz\CoreBundle\Entity\CustomForm;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * Override CustomForm default normalization.
 */
final class CustomFormNormalizer extends AbstractPathNormalizer
{
    #[\Override]
    public function normalize(mixed $data, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $normalized = $this->decorated->normalize($data, $format, $context);
        if ($data instanceof CustomForm && is_array($normalized)) {
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
        }

        return $normalized;
    }
}
