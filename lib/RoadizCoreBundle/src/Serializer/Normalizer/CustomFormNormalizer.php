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
    /**
     * @return array|\ArrayObject|bool|float|int|mixed|string|null
     *
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    #[\Override]
    public function normalize(mixed $object, ?string $format = null, array $context = []): mixed
    {
        $data = $this->decorated->normalize($object, $format, $context);
        if ($object instanceof CustomForm && is_array($data)) {
            $data['name'] = $object->getDisplayName();
            $data['color'] = $object->getColor();
            $data['description'] = $object->getDescription();
            $data['slug'] = (new AsciiSlugger())->slug($object->getName())->snake()->toString();
            $data['open'] = $object->isFormStillOpen();

            if (
                isset($context['groups'])
                && \in_array('urls', $context['groups'], true)
            ) {
                $data['definitionUrl'] = $this->urlGenerator->generate('api_custom_forms_item_definition', [
                    'id' => $object->getId(),
                ]);
                $data['postUrl'] = $this->urlGenerator->generate('api_custom_forms_item_post', [
                    'id' => $object->getId(),
                ]);
            }
        }

        return $data;
    }
}
