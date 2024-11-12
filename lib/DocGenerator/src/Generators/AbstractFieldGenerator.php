<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documentation\Generators;

use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractFieldGenerator
{
    protected NodeTypeFieldInterface $field;
    protected TranslatorInterface $translator;
    protected ParameterBag $nodeTypesBag;
    protected MarkdownGeneratorFactory $markdownGeneratorFactory;

    public function __construct(
        MarkdownGeneratorFactory $fieldGeneratorFactory,
        NodeTypeFieldInterface $field,
        ParameterBag $nodeTypesBag,
        TranslatorInterface $translator,
    ) {
        $this->field = $field;
        $this->nodeTypesBag = $nodeTypesBag;
        $this->translator = $translator;
        $this->markdownGeneratorFactory = $fieldGeneratorFactory;
    }

    abstract public function getContents(): string;

    public function getIntroduction(): string
    {
        $lines = [
            '### '.$this->field->getLabel(),
        ];
        if (!empty($this->field->getDescription())) {
            $lines[] = $this->field->getDescription();
        }
        $lines = array_merge($lines, [
            '',
            '|     |     |',
            '| --- | --- |',
            '| **'.trim($this->translator->trans('docs.type')).'** | '.$this->translator->trans($this->field->getTypeName()).' |',
            '| **'.trim($this->translator->trans('docs.technical_name')).'** | `'.$this->field->getVarName().'` |',
            '| **'.trim($this->translator->trans('docs.universal')).'** | *'.$this->markdownGeneratorFactory->getHumanBool($this->field->isUniversal()).'* |',
        ]);

        if (!empty($this->field->getGroupName())) {
            $lines[] = '| **'.trim($this->translator->trans('docs.group')).'** | '.$this->field->getGroupName().' |';
        }

        if (!$this->field->isVisible()) {
            $lines[] = '| **'.trim($this->translator->trans('docs.visible')).'** | *'.$this->markdownGeneratorFactory->getHumanBool($this->field->isVisible()).'* |';
        }

        return implode("\n", $lines)."\n";
    }
}
