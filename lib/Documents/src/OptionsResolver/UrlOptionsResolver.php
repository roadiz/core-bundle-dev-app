<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\OptionsResolver;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UrlOptionsResolver extends OptionsResolver
{
    public function __construct()
    {
        $this->setDefaults(
            [
            'crop' => null,
            'fit' => null,
            'align' => null,
            'background' => null,
            'absolute' => false,
            'grayscale' => false,
            'progressive' => false,
            'noProcess' => false,
            'interlace' => false,
            'width' => 0,
            'flip' => null,
            'height' => 0,
            'quality' => 90,
            'blur' => 0,
            'sharpen' => 0,
            'contrast' => 0,
            'rotate' => 0,
            'ratio' => null,
            ]
        );
        $this->setAllowedTypes('width', ['int']);
        $this->setAllowedTypes('height', ['int']);
        $this->setAllowedTypes('crop', ['null', 'string']);
        $this->setAllowedTypes('fit', ['null', 'string']);
        $this->setAllowedTypes('flip', ['null', 'string']);
        $this->setAllowedTypes('align', ['null', 'string']);
        $this->setAllowedTypes('ratio', ['null', 'float']);
        $this->setAllowedValues(
            'align',
            [
            null,
            'top-left',
            'top',
            'top-right',
            'left',
            'center',
            'right',
            'bottom-left',
            'bottom',
            'bottom-right',
            ]
        );
        $this->setAllowedTypes('background', ['null', 'string']);
        $this->setAllowedTypes('quality', ['int']);
        $this->setAllowedTypes('blur', ['int']);
        $this->setAllowedTypes('sharpen', ['int']);
        $this->setAllowedTypes('contrast', ['int']);
        $this->setAllowedTypes('rotate', ['int']);
        $this->setAllowedTypes('absolute', ['boolean']);
        $this->setAllowedTypes('grayscale', ['boolean']);
        $this->setAllowedTypes('progressive', ['boolean']);
        $this->setAllowedTypes('noProcess', ['boolean']);
        $this->setAllowedTypes('interlace', ['boolean']);

        $this->setDefault(
            'ratio',
            function (Options $options) {
                /** @var \ArrayAccess<string, string|null> $options */
                $compositing = $options['crop'] ?? $options['fit'] ?? '';
                if (1 === preg_match('#(?<width>[0-9]+)[x:\.](?<height>[0-9]+)#', $compositing, $matches)) {
                    return ((float) $matches['width']) / ((float) $matches['height']);
                }
                return null;
            }
        );
        /*
         * Guess width and height options from fit
         */
        $this->setDefault(
            'width',
            function (Options $options) {
                /** @var \ArrayAccess<string, string|null> $options */
                $compositing = $options['fit'] ?? '';
                if (1 === preg_match('#(?<width>[0-9]+)[x:\.](?<height>[0-9]+)#', $compositing, $matches)) {
                    return (int) $matches['width'];
                } elseif (null !== $options['ratio'] && $options['height'] !== 0 && $options['ratio'] !== 0) {
                    return (int) (intval($options['height']) * floatval($options['ratio']));
                }
                return 0;
            }
        );
        $this->setDefault(
            'height',
            function (Options $options) {
                /** @var \ArrayAccess<string, string|null> $options */
                $compositing = $options['fit'] ?? '';
                if (1 === preg_match('#(?<width>[0-9]+)[x:\.](?<height>[0-9]+)#', $compositing, $matches)) {
                    return (int) $matches['height'];
                } elseif (null !== $options['ratio'] && $options['width'] !== 0 && $options['ratio'] !== 0) {
                    return (int) (intval($options['width']) / floatval($options['ratio']));
                }
                return 0;
            }
        );
    }
}
