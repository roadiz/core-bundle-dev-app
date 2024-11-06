<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\UrlGenerators;

/**
 * Compile Intervention Request options into a single query string.
 */
class OptionsCompiler
{
    private array $options = [];

    /**
     * Compile Intervention Request options into a single query string.
     *
     * @param array $options Resolved options
     */
    public function compile(array $options): string
    {
        /*
         * Options passed to compiler should already be resolved not to
         * trigger resolving twice.
         */
        $this->options = $options;
        $shortOptions = [];

        if (null === $this->options['fit'] && $this->options['width'] > 0) {
            $shortOptions['w'] = 'w'.(int) $this->options['width'];
        }
        if (null === $this->options['fit'] && $this->options['height'] > 0) {
            $shortOptions['h'] = 'h'.(int) $this->options['height'];
        }
        if (null !== $this->options['crop']) {
            $shortOptions['c'] = 'c'.strip_tags($this->options['crop']);
        }
        if ($this->options['blur'] > 0) {
            $shortOptions['l'] = 'l'.$this->options['blur'];
        }
        if (null !== $this->options['fit']) {
            $shortOptions['f'] = 'f'.strip_tags($this->options['fit']);
        }
        if (null !== $this->options['flip']) {
            $shortOptions['m'] = 'm'.trim(strip_tags($this->options['flip']));
        }
        if ($this->options['rotate'] > 0) {
            $shortOptions['r'] = 'r'.$this->options['rotate'];
        }
        if ($this->options['sharpen'] > 0) {
            $shortOptions['s'] = 's'.$this->options['sharpen'];
        }
        if ($this->options['contrast'] > 0) {
            $shortOptions['k'] = 'k'.$this->options['contrast'];
        }
        if ($this->options['grayscale']) {
            $shortOptions['g'] = 'g1';
        }
        if ($this->options['quality'] > 0) {
            $shortOptions['q'] = 'q'.$this->options['quality'];
        }
        if (null !== $this->options['background']) {
            $shortOptions['b'] = 'b'.strip_tags($this->options['background']);
        }
        if ($this->options['progressive']) {
            $shortOptions['p'] = 'p1';
        }
        if ($this->options['interlace']) {
            $shortOptions['i'] = 'i1';
        }

        $availablePosition = [
            'tl' => 'top-left',
            't' => 'top',
            'tr' => 'top-right',
            'l' => 'left',
            'c' => 'center',
            'r' => 'right',
            'bl' => 'bottom-left',
            'b' => 'bottom',
            'br' => 'bottom-right',
        ];
        $availablePositionShort = array_flip($availablePosition);
        if (
            null !== $this->options['align']
            && isset($availablePositionShort[$this->options['align']])
        ) {
            $shortOptions['a'] = 'a'.$availablePositionShort[$this->options['align']];
        }

        return implode('-', $shortOptions);
    }
}
