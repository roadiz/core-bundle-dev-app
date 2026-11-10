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

        foreach ($this->translatableValues($source, $fields) as $unit) {
            $source->{$unit['setter']}($this->translateValue(
                $unit['value'],
                $unit['maxLength'],
                $unit['isHtml'],
                $sourceLang,
                $targetLang,
            ));
        }
    }

    /**
     * Number of characters this source would send to the provider — what DeepL actually bills.
     *
     * Markup counts: richtext is sent with tag_handling=html, and maxLength truncates the
     * *response*, never the request, so neither lowers the bill.
     *
     * @param iterable<NodeTypeField> $fields
     */
    public function countTranslatableCharacters(NodesSources $source, iterable $fields): int
    {
        $characters = 0;
        foreach ($this->translatableValues($source, $fields) as $unit) {
            $characters += mb_strlen($unit['value']);
        }

        return $characters;
    }

    /**
     * The single source of truth for what reaches the provider: translate() applies it,
     * countTranslatableCharacters() prices it. Any divergence would make the estimate lie.
     *
     * @param iterable<NodeTypeField> $fields
     *
     * @return \Generator<array{value: string, setter: string, maxLength: int|null, isHtml: bool}>
     */
    private function translatableValues(NodesSources $source, iterable $fields): \Generator
    {
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
            yield [
                'value' => $value,
                'setter' => $field->getSetterName(),
                'maxLength' => $field->getMaxLength(),
                'isHtml' => FieldType::RICHTEXT_T === $field->getType(),
            ];
        }

        // Base NodesSources columns, with their own column lengths.
        foreach ([
            ['getTitle', 'setTitle', 250],
            ['getMetaTitle', 'setMetaTitle', 150],
            ['getMetaDescription', 'setMetaDescription', null],
        ] as [$getter, $setter, $maxLength]) {
            $value = $source->{$getter}();
            if (!is_string($value) || '' === trim($value)) {
                continue;
            }
            yield [
                'value' => $value,
                'setter' => $setter,
                'maxLength' => $maxLength,
                'isHtml' => false,
            ];
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
