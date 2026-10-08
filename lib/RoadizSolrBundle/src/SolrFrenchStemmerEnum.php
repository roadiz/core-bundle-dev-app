<?php

declare(strict_types=1);

namespace RZ\Roadiz\SolrBundle;

enum SolrFrenchStemmerEnum: string
{
    case LIGHT = 'light';
    case MINIMAL = 'minimal';
    case NONE = 'none';

    /**
     * @return string|null Solr filter name, null when stemming is disabled
     */
    public function getFilterName(): ?string
    {
        return match ($this) {
            self::LIGHT => 'frenchLightStem',
            self::MINIMAL => 'frenchMinimalStem',
            self::NONE => null,
        };
    }
}
