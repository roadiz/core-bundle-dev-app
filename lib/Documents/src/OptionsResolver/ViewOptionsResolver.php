<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\OptionsResolver;

class ViewOptionsResolver extends UrlOptionsResolver
{
    public function __construct()
    {
        parent::__construct();

        $this->setDefaults([
            'alt' => null,
            'autoplay' => false,
            'blurredFallback' => false,
            'class' => null,
            'controls' => true,
            'custom_poster' => null,
            'embed' => false,
            'fallback' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNcvGDBfwAGtQLk4581vAAAAABJRU5ErkJggg==',
            'fullscreen' => true,
            'id' => null,
            'identifier' => null,
            'inline' => true,
            'lazyload' => false,
            'lazyload_class' => 'lazyload',
            'loading' => null,
            'loop' => false,
            'media' => [],
            'muted' => false,
            'picture' => false,
            'sizes' => [],
            'srcset' => [],
            'title' => null,
            // prevent thumbnail usage when available
            'no_thumbnail' => false,
            /*
             * Soundcloud
             */
            'hide_related' => false,
            'show_artwork' => false,
            'show_comments' => false,
            'show_reposts' => false,
            'show_user' => false,
            'visual' => false,
            /*
             * Vimeo
             */
            'api' => true,
            'automute' => false,
            'autopause' => false,
            'byline' => false,
            'color' => null,
            'displayTitle' => false,
            'portrait' => false,
            /*
             * Youtube
             */
            'enablejsapi' => true,
            'end' => false,
            'modestbranding' => true,
            'playlist' => false,
            'playsinline' => false, // Allow iframe to play inline on iOS
            'rel' => false,
            'showinfo' => false,
            'start' => false,
            /*
             * Mixcloud
             */
            'hide_artwork' => false,
            'hide_cover' => true,
            'light' => true,
            'mini' => false,
        ]);

        $this->setAllowedTypes('alt', ['null', 'string']);
        $this->setAllowedTypes('autoplay', ['boolean']);
        $this->setAllowedTypes('blurredFallback', ['boolean']);
        $this->setAllowedTypes('class', ['null', 'string']);
        $this->setAllowedTypes('controls', ['boolean']);
        $this->setAllowedTypes('custom_poster', ['null', 'string']);
        $this->setAllowedTypes('embed', ['boolean']);
        $this->setAllowedTypes('fullscreen', ['boolean']);
        $this->setAllowedTypes('id', ['null', 'string']);
        $this->setAllowedTypes('identifier', ['null', 'string']);
        $this->setAllowedTypes('inline', ['boolean']);
        $this->setAllowedTypes('lazyload', ['boolean']);
        $this->setAllowedTypes('lazyload_class', ['string']);
        $this->setAllowedTypes('loop', ['boolean']);
        $this->setAllowedTypes('media', ['array']);
        $this->setAllowedTypes('muted', ['boolean']);
        $this->setAllowedTypes('no_thumbnail', ['boolean']);
        $this->setAllowedTypes('picture', ['boolean']);
        $this->setAllowedTypes('sizes', ['array']);
        $this->setAllowedTypes('srcset', ['array']);
        $this->setAllowedTypes('title', ['null', 'string']);

        // Fallback src content when using lazyload with data-src
        $this->setAllowedTypes('fallback', ['string']);

        // Native lazyload support
        $this->setAllowedTypes('loading', ['null', 'string']);
        $this->setAllowedValues('loading', [null, 'auto', 'eager', 'lazy']);

        // Soundcloud
        $this->setAllowedTypes('hide_related', ['boolean']);
        $this->setAllowedTypes('show_comments', ['boolean']);
        $this->setAllowedTypes('show_user', ['boolean']);
        $this->setAllowedTypes('show_reposts', ['boolean']);
        $this->setAllowedTypes('show_artwork', ['boolean']);
        $this->setAllowedTypes('visual', ['boolean']);

        // Vimeo
        $this->setAllowedTypes('displayTitle', ['boolean']);
        $this->setAllowedTypes('byline', ['boolean']);
        $this->setAllowedTypes('portrait', ['boolean']);
        $this->setAllowedTypes('automute', ['boolean']);
        $this->setAllowedTypes('autopause', ['boolean']);
        $this->setAllowedTypes('color', ['null', 'string']);
        $this->setAllowedTypes('api', ['boolean']);

        // Youtube
        $this->setAllowedTypes('modestbranding', ['boolean']);
        $this->setAllowedTypes('rel', ['boolean']);
        $this->setAllowedTypes('showinfo', ['boolean']);
        $this->setAllowedTypes('start', ['boolean', 'integer']);
        $this->setAllowedTypes('end', ['boolean', 'integer']);
        $this->setAllowedTypes('enablejsapi', ['boolean']);
        $this->setAllowedTypes('playsinline', ['boolean']);

        /*
         * Mixcloud
         */
        $this->setAllowedTypes('mini', ['boolean']);
        $this->setAllowedTypes('light', ['boolean']);
        $this->setAllowedTypes('hide_cover', ['boolean']);
        $this->setAllowedTypes('hide_artwork', ['boolean']);
    }
}
