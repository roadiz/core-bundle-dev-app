<?php

declare(strict_types=1);

namespace RZ\Roadiz\SolrBundle;

enum SolrHighlightingMethodEnum: string
{
    case UNIFIED = 'unified';
    case ORIGINAL = 'original';
    case FAST_VECTOR = 'fastVector';
}
