<?php

declare(strict_types=1);

namespace RZ\Roadiz\SolrBundle;

enum SolrHighlightingBsTypeEnum: string
{
    case SEPARATOR = 'SEPARATOR';
    case SENTENCE = 'SENTENCE';
    case WORD = 'WORD';
    case CHARACTER = 'CHARACTER';
    case LINE = 'LINE';
    case WHOLE = 'WHOLE';
}
