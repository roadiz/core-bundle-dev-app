<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documentation\Generators;

use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;
use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class NodeTypeGenerator
{
    protected array $fieldGenerators = [];

    public function __construct(
        protected NodeTypeInterface $nodeType,
        protected TranslatorInterface $translator,
        protected MarkdownGeneratorFactory $markdownGeneratorFactory,
    ) {
        /** @var NodeTypeFieldInterface $field */
        foreach ($this->nodeType->getFields() as $field) {
            $this->fieldGenerators[] = $this->markdownGeneratorFactory->createForNodeTypeField($field);
        }
    }

    public function getMenuEntry(): string
    {
        return '['.$this->nodeType->getLabel().']('.$this->getPath().')';
    }

    public function getType(): string
    {
        return $this->nodeType->isReachable() ? 'page' : 'block';
    }

    public function getPath(): string
    {
        return $this->getType().'/'.$this->nodeType->getName().'.md';
    }

    public function getContents(): string
    {
        return implode("\n\n", [
            $this->getIntroduction(),
            '## '.$this->translator->trans('docs.fields'),
            $this->getFieldsContents(),
        ]);
    }

    protected function getIntroduction(): string
    {
        $lines = [
            '# '.$this->nodeType->getLabel(),
        ];
        if (!empty($this->nodeType->getDescription())) {
            $lines[] = $this->nodeType->getDescription();
        }
        $lines = array_merge($lines, [
            '',
            '|     |     |',
            '| --- | --- |',
            '| **'.trim($this->translator->trans('docs.technical_name')).'** | `'.$this->nodeType->getName().'` |',
        ]);

        if ($this->nodeType->isPublishable()) {
            $lines[] = '| **'.trim($this->translator->trans('docs.publishable')).'** | *'.$this->markdownGeneratorFactory->getHumanBool($this->nodeType->isPublishable()).'* |';
        }
        if (!$this->nodeType->isVisible()) {
            $lines[] = '| **'.trim($this->translator->trans('docs.visible')).'** | *'.$this->markdownGeneratorFactory->getHumanBool($this->nodeType->isVisible()).'* |';
        }

        return implode("\n", $lines);
    }

    protected function getFieldsContents(): string
    {
        return implode("\n", array_map(function (AbstractFieldGenerator $abstractFieldGenerator) {
            return $abstractFieldGenerator->getContents();
        }, $this->fieldGenerators));
    }
}
