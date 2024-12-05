<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documentation\Generators;

use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractFieldGenerator
{
    public function __construct(
        protected readonly MarkdownGeneratorFactory $markdownGeneratorFactory,
        protected readonly NodeTypeFieldInterface $field,
        protected readonly ParameterBag $nodeTypesBag,
        protected readonly TranslatorInterface $translator,
    ) {
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
