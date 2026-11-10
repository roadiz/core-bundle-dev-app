<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Liform;

use Limenius\Liform\Transformer\ExtensionInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Translates the schema's `attr.placeholder` through the translator.
 *
 * Upstream limenius/liform does not translate placeholders; the rezozero/liform
 * fork did it inside AbstractTransformer::addAttr(). Since that lived in the base
 * class (affecting every transformer), we replicate it as a Liform extension,
 * which runs after addAttr() for all form types — no library fork required.
 */
final readonly class PlaceholderTranslationExtension implements ExtensionInterface
{
    public function __construct(private TranslatorInterface $translator)
    {
    }

    public function apply(FormInterface $form, array $schema): array
    {
        if (!empty($schema['attr']['placeholder']) && is_string($schema['attr']['placeholder'])) {
            $translationDomain = $form->getConfig()->getOption('translation_domain');
            $schema['attr']['placeholder'] = $this->translator->trans($schema['attr']['placeholder'], [], $translationDomain);
        }

        return $schema;
    }
}
