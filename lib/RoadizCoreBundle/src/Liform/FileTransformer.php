<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Liform;

use Limenius\Liform\Transformer\AbstractTransformer;
use Symfony\Component\Form\FormInterface;

/**
 * Adds FileType support (including the `multiple` option) on top of the
 * upstream limenius/liform transformers, which do not ship a file transformer.
 */
final class FileTransformer extends AbstractTransformer
{
    public function transform(FormInterface $form, array $extensions = [], ?string $widget = null): array
    {
        $schema = ['type' => 'string'];
        $schema = $this->addCommonSpecs($form, $schema, $extensions, $widget);

        return $this->addMultiple($form, $schema);
    }

    private function addMultiple(FormInterface $form, array $schema): array
    {
        if ($form->getConfig()->getOption('multiple')) {
            $schema['attr']['multiple'] = true;
        }

        return $schema;
    }

    protected function addWidget(FormInterface $form, array $schema, mixed $configWidget): array
    {
        $schema['widget'] = 'file';

        return $schema;
    }
}
