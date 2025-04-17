<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\OptionsResolver;

use Symfony\Component\OptionsResolver\OptionsResolver;

class UrlOptionsResolver extends OptionsResolver
{
    public function __construct()
    {
        $this->setDefaults([
            'absolute' => false,
            'align' => null,
            'background' => null,
            'blur' => 0,
            'contrast' => 0,
            'crop' => null,
            'fit' => null,
            'flip' => null,
            'grayscale' => false,
            'height' => 0,
            'hotspot' => null,
            'interlace' => false,
            'noProcess' => false,
            'progressive' => false,
            'quality' => 90,
            'ratio' => null,
            'rotate' => 0,
            'sharpen' => 0,
            'width' => 0,
        ]);
        $this->setAllowedTypes('absolute', ['boolean']);
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
        $this->setAllowedTypes('align', ['null', 'string']);
        $this->setAllowedTypes('background', ['null', 'string']);
        $this->setAllowedTypes('blur', ['int']);
        $this->setAllowedTypes('contrast', ['int']);
        $this->setAllowedTypes('crop', ['null', 'string']);
        $this->setAllowedTypes('fit', ['null', 'string']);
        $this->setAllowedTypes('flip', ['null', 'string']);
        $this->setAllowedTypes('grayscale', ['boolean']);
        $this->setAllowedTypes('height', ['int']);
        $this->setAllowedTypes('hotspot', ['null', 'string']);
        $this->setAllowedTypes('interlace', ['boolean']);
        $this->setAllowedTypes('noProcess', ['boolean']);
        $this->setAllowedTypes('progressive', ['boolean']);
        $this->setAllowedTypes('quality', ['int']);
        $this->setAllowedTypes('ratio', ['null', 'float']);
        $this->setAllowedTypes('rotate', ['int']);
        $this->setAllowedTypes('sharpen', ['int']);
        $this->setAllowedTypes('width', ['int']);
    }
}
