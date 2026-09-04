<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\TranslateAssistant;

use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use RZ\Roadiz\CoreBundle\Enum\FieldType;

/**
 * Translates every translatable field of a node-source in place, using a translate-assistant provider.
 *
 * Fields are passed in instead of being resolved from the NodeTypes bag: the bag is final, so
 * injecting it would make this class untestable without a container.
 */
final readonly class NodesSourcesTranslator
{
    public function __construct(private TranslateAssistantInterface $assistant)
    {
    }

    /**
     * @param iterable<NodeTypeField> $fields
     */
    public function translate(
        NodesSources $source,
        iterable $fields,
        string $sourceLocale,
        string $targetLocale,
    ): void {
        // DeepL only accepts primary languages, Translation locales may be regional (fr_CA).
        $sourceLang = \Locale::getPrimaryLanguage($sourceLocale) ?? $sourceLocale;
        $targetLang = \Locale::getPrimaryLanguage($targetLocale) ?? $targetLocale;

        foreach ($fields as $field) {
            if (!in_array($field->getType(), FieldType::translatableTypes(), true)) {
                continue;
            }
            if ($field->isUniversal() || $field->isExcludedFromTranslation()) {
                continue;
            }
            $value = $source->{$field->getGetterName()}();
            if (!is_string($value) || '' === trim($value)) {
                continue;
            }
            $source->{$field->getSetterName()}($this->translateValue(
                $value,
                $field->getMaxLength(),
                FieldType::RICHTEXT_T === $field->getType(),
                $sourceLang,
                $targetLang,
            ));
        }

        // Base NodesSources columns, with their own column lengths.
        if ('' !== trim($source->getTitle() ?? '')) {
            $source->setTitle($this->translateValue((string) $source->getTitle(), 250, false, $sourceLang, $targetLang));
        }
        if ('' !== trim($source->getMetaTitle())) {
            $source->setMetaTitle($this->translateValue($source->getMetaTitle(), 150, false, $sourceLang, $targetLang));
        }
        if ('' !== trim($source->getMetaDescription())) {
            $source->setMetaDescription($this->translateValue($source->getMetaDescription(), null, false, $sourceLang, $targetLang));
        }
    }

    private function translateValue(string $text, ?int $maxLength, bool $isHtml, string $sourceLang, string $targetLang): string
    {
        $output = $this->assistant->translate(new TranslateAssistantInput(
            text: $text,
            targetLang: $targetLang,
            sourceLang: $sourceLang,
            options: $isHtml ? ['tag_handling' => 'html'] : [],
        ));

        return null !== $maxLength
            ? mb_substr($output->translatedText, 0, $maxLength)
            : $output->translatedText;
    }
}
