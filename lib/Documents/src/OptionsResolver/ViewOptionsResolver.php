<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\OptionsResolver;

class ViewOptionsResolver extends UrlOptionsResolver
{
    public function __construct()
    {
        parent::__construct();
        $this->setDefaults([
            'identifier' => null,
            'id' => null,
            'class' => null,
            'alt' => null,
            'title' => null,
            'custom_poster' => null,
            'embed' => false,
            'lazyload' => false,
            'lazyload_class' => 'lazyload',
            'inline' => true,
            'autoplay' => false,
            'muted' => false,
            'loop' => false,
            'controls' => true,
            'fullscreen' => true,
            'loading' => null,
            'fallback' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNcvGDBfwAGtQLk4581vAAAAABJRU5ErkJggg==',
            'blurredFallback' => false,
            'media' => [],
            'srcset' => [],
            'sizes' => [],
            'picture' => false,
            // prevent thumbnail usage when available
            'no_thumbnail' => false,
            /*
             * Soundcloud
             */
            'hide_related' => false,
            'show_comments' => false,
            'show_user' => false,
            'show_reposts' => false,
            'show_artwork' => false,
            'visual' => false,
            /*
             * Vimeo
             */
            'displayTitle' => false,
            'byline' => false,
            'portrait' => false,
            'color' => null,
            'api' => true,
            'automute' => false,
            'autopause' => false,
            /*
             * Youtube
             */
            'modestbranding' => true,
            'rel' => false,
            'showinfo' => false,
            'start' => false,
            'end' => false,
            'enablejsapi' => true,
            'playlist' => false,
            'playsinline' => false, // Allow iframe to play inline on iOS
            /*
             * Mixcloud
             */
            'mini' => false,
            'light' => true,
            'hide_cover' => true,
            'hide_artwork' => false,
        ]);

        $this->setAllowedTypes('identifier', ['null', 'string']);
        $this->setAllowedTypes('id', ['null', 'string']);
        $this->setAllowedTypes('class', ['null', 'string']);
        $this->setAllowedTypes('alt', ['null', 'string']);
        $this->setAllowedTypes('title', ['null', 'string']);
        $this->setAllowedTypes('custom_poster', ['null', 'string']);
        $this->setAllowedTypes('embed', ['boolean']);
        $this->setAllowedTypes('lazyload', ['boolean']);
        $this->setAllowedTypes('lazyload_class', ['string']);
        $this->setAllowedTypes('inline', ['boolean']);
        $this->setAllowedTypes('autoplay', ['boolean']);
        $this->setAllowedTypes('muted', ['boolean']);
        $this->setAllowedTypes('blurredFallback', ['boolean']);
        $this->setAllowedTypes('loop', ['boolean']);
        $this->setAllowedTypes('controls', ['boolean']);
        $this->setAllowedTypes('fullscreen', ['boolean']);
        $this->setAllowedTypes('srcset', ['array']);
        $this->setAllowedTypes('media', ['array']);
        $this->setAllowedTypes('sizes', ['array']);
        $this->setAllowedTypes('picture', ['boolean']);
        $this->setAllowedTypes('no_thumbnail', ['boolean']);

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
