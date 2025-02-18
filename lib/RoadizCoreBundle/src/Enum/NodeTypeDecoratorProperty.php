<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Enum;

enum NodeTypeDecoratorProperty: string
{
    // String Value
    case NODE_TYPE_DISPLAY_NAME = 'displayName';
    // String Value
    case NODE_TYPE_DESCRIPTION = 'description';
    // String Value
    case NODE_TYPE_COLOR = 'color';
    // String Value
    case NODE_TYPE_FIELD_LABEL = 'field_label';
    // Boolean Value
    case NODE_TYPE_FIELD_UNIVERSAL = 'field_universal';
    // String Value
    case NODE_TYPE_FIELD_DESCRIPTION = 'field_description';
    // String Value
    case NODE_TYPE_FIELD_PLACEHOLDER = 'field_placeholder';
    // Boolean Value
    case NODE_TYPE_FIELD_VISIBLE = 'field_visible';
    // Integer Value
    case NODE_TYPE_FIELD_MIN_LENGTH = 'field_min_length';
    // Integer Value
    case NODE_TYPE_FIELD_MAX_LENGTH = 'field_max_length';

    public function isNodeTypeProperty(): bool
    {
        return in_array($this->value, [
            self::NODE_TYPE_DISPLAY_NAME->value,
            self::NODE_TYPE_DESCRIPTION->value,
            self::NODE_TYPE_COLOR->value,
        ], true);
    }
}
