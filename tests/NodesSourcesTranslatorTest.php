<?php

declare(strict_types=1);

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Enum\FieldType;
use RZ\Roadiz\RozierBundle\TranslateAssistant\NodesSourcesTranslator;
use RZ\Roadiz\RozierBundle\TranslateAssistant\TranslateAssistantInput;
use RZ\Roadiz\RozierBundle\TranslateAssistant\TranslateAssistantInterface;
use RZ\Roadiz\RozierBundle\TranslateAssistant\TranslateAssistantOutput;
use RZ\Roadiz\RozierBundle\TranslateAssistant\TranslateAssistantUsage;

final class NodesSourcesTranslatorTest extends TestCase
{
    public function testTranslatesProseFields(): void
    {
        $source = $this->createSource();
        $source->setSubTitle('Bonjour');

        $this->translate($source, [$this->field('subTitle', FieldType::STRING_T)]);

        $this->assertSame('[Bonjour]', $source->getSubTitle());
    }

    public function testDoesNotTranslateExcludedField(): void
    {
        $source = $this->createSource();
        $source->setSubTitle('Bonjour');

        $field = $this->field('subTitle', FieldType::STRING_T)->setExcludeFromTranslation(true);
        $this->translate($source, [$field]);

        $this->assertSame('Bonjour', $source->getSubTitle());
    }

    public function testDoesNotTranslateUniversalField(): void
    {
        $source = $this->createSource();
        $source->setSubTitle('Bonjour');

        $field = $this->field('subTitle', FieldType::STRING_T)->setUniversal(true);
        $this->translate($source, [$field]);

        $this->assertSame('Bonjour', $source->getSubTitle());
    }

    public function testTranslatesFieldExcludedFromSearch(): void
    {
        $source = $this->createSource();
        $source->setSubTitle('Bonjour');

        $field = $this->field('subTitle', FieldType::STRING_T)->setExcludeFromSearch(true);
        $this->translate($source, [$field]);

        $this->assertSame('[Bonjour]', $source->getSubTitle());
    }

    /**
     * @dataProvider nonTranslatableTypes
     */
    public function testDoesNotTranslateNonProseType(FieldType $type): void
    {
        $source = $this->createSource();
        $source->setSubTitle('#ff0000');

        $this->translate($source, [$this->field('subTitle', $type)]);

        $this->assertSame('#ff0000', $source->getSubTitle());
    }

    /**
     * @return array<string, array{FieldType}>
     */
    public static function nonTranslatableTypes(): array
    {
        return [
            'colour' => [FieldType::COLOUR_T],
            'enum' => [FieldType::ENUM_T],
            'yaml' => [FieldType::YAML_T],
        ];
    }

    public function testDoesNotCallProviderOnEmptySource(): void
    {
        $assistant = $this->assistant();
        $translator = new NodesSourcesTranslator($assistant);

        $source = $this->createSource();
        $source->setSubTitle('   ');

        $translator->translate($source, [$this->field('subTitle', FieldType::STRING_T)], 'fr', 'en');

        $this->assertSame(0, $assistant->calls);
    }

    public function testTruncatesMetaTitleToColumnLength(): void
    {
        $source = $this->createSource();
        $source->setMetaTitle(str_repeat('a', 149));

        $this->translate($source, []);

        $this->assertSame(150, mb_strlen($source->getMetaTitle()));
    }

    public function testNormalizesRegionalLocales(): void
    {
        $assistant = $this->assistant();
        $translator = new NodesSourcesTranslator($assistant);

        $source = $this->createSource();
        $source->setTitle('Bonjour');

        $translator->translate($source, [], 'fr_CA', 'en_GB');

        $this->assertSame('fr', $assistant->lastInput?->sourceLang);
        $this->assertSame('en', $assistant->lastInput?->targetLang);
    }

    /**
     * @param iterable<NodeTypeField> $fields
     */
    private function translate(NodesSources $source, iterable $fields): void
    {
        (new NodesSourcesTranslator($this->assistant()))->translate($source, $fields, 'fr', 'en');
    }

    private function field(string $name, FieldType $type): NodeTypeField
    {
        $field = new NodeTypeField();
        $field->setName($name);
        $field->setType($type);

        return $field;
    }

    private function createSource(): NodesSources
    {
        return new class(new Node(), new Translation()) extends NodesSources {
            private ?string $subTitle = null;

            public function getSubTitle(): ?string
            {
                return $this->subTitle;
            }

            public function setSubTitle(?string $subTitle): static
            {
                $this->subTitle = $subTitle;

                return $this;
            }
        };
    }

    private function assistant(): TranslateAssistantInterface
    {
        return new class implements TranslateAssistantInterface {
            public int $calls = 0;
            public ?TranslateAssistantInput $lastInput = null;

            #[\Override]
            public function translate(TranslateAssistantInput $translatorDto): TranslateAssistantOutput
            {
                ++$this->calls;
                $this->lastInput = $translatorDto;

                return new TranslateAssistantOutput(
                    originalText: $translatorDto->text,
                    translatedText: '['.$translatorDto->text.']',
                    sourceLang: $translatorDto->sourceLang ?? '',
                    targetLang: $translatorDto->targetLang,
                );
            }

            #[\Override]
            public function rephrase(TranslateAssistantInput $translatorDto): TranslateAssistantOutput
            {
                throw new \LogicException('Not used.');
            }

            #[\Override]
            public function supportRephrase(): bool
            {
                return false;
            }

            #[\Override]
            public function usage(): ?TranslateAssistantUsage
            {
                return null;
            }
        };
    }
}
