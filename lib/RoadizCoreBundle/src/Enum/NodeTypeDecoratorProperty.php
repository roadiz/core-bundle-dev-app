<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Enum;

enum NodeTypeDecoratorProperty: string
{
    // String Value
    case NODE_TYPE_DISPLAY_NAME = 'display_name';
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
            self::NODE_TYPE_COLOR->value,
        ], true);
    }

    public function isStringType(): bool
    {
        return in_array($this->value, [
            self::NODE_TYPE_DISPLAY_NAME->value,
            self::NODE_TYPE_DESCRIPTION->value,
            self::NODE_TYPE_COLOR->value,
            self::NODE_TYPE_FIELD_LABEL->value,
            self::NODE_TYPE_FIELD_DESCRIPTION->value,
            self::NODE_TYPE_FIELD_PLACEHOLDER->value,
        ], true);
    }

    public function isIntegerType(): bool
    {
        return in_array($this->value, [
            self::NODE_TYPE_FIELD_MIN_LENGTH->value,
            self::NODE_TYPE_FIELD_MAX_LENGTH->value,
        ], true);
    }

    public function isBooleanType(): bool
    {
        return in_array($this->value, [
            self::NODE_TYPE_FIELD_UNIVERSAL->value,
            self::NODE_TYPE_FIELD_VISIBLE->value,
        ], true);
    }
}
