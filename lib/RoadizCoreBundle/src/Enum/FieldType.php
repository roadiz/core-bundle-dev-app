<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Enum;

enum FieldType: int
{
    case STRING_T = 0;
    case DATETIME_T = 1;
    case TEXT_T = 2;
    case RICHTEXT_T = 3;
    case MARKDOWN_T = 4;
    case BOOLEAN_T = 5;
    case INTEGER_T = 6;
    case DECIMAL_T = 7;
    case EMAIL_T = 8;
    case DOCUMENTS_T = 9;
    case PASSWORD_T = 10;
    case COLOUR_T = 11;
    case GEOTAG_T = 12;
    case NODES_T = 13;
    case USER_T = 14;
    case ENUM_T = 15;
    case CHILDREN_T = 16;
    case CUSTOM_FORMS_T = 17;
    case MULTIPLE_T = 18;
    case RADIO_GROUP_T = 19;
    case CHECK_GROUP_T = 20;
    case MULTI_GEOTAG_T = 21;
    case DATE_T = 22;
    case JSON_T = 23;
    case CSS_T = 24;
    case COUNTRY_T = 25;
    case YAML_T = 26;
    case MANY_TO_MANY_T = 27;
    case MANY_TO_ONE_T = 28;
    case MULTI_PROVIDER_T = 29;
    case SINGLE_PROVIDER_T = 30;
    case COLLECTION_T = 31;

    /**
     * @return array<int, string>
     */
    protected static function toHuman(): array
    {
        return [
            FieldType::STRING_T->value => 'string.type',
            FieldType::DATETIME_T->value => 'date-time.type',
            FieldType::DATE_T->value => 'date.type',
            FieldType::TEXT_T->value => 'text.type',
            FieldType::MARKDOWN_T->value => 'markdown.type',
            FieldType::BOOLEAN_T->value => 'boolean.type',
            FieldType::INTEGER_T->value => 'integer.type',
            FieldType::DECIMAL_T->value => 'decimal.type',
            FieldType::EMAIL_T->value => 'email.type',
            FieldType::ENUM_T->value => 'single-choice.type',
            FieldType::MULTIPLE_T->value => 'multiple-choice.type',
            FieldType::DOCUMENTS_T->value => 'documents.type',
            FieldType::NODES_T->value => 'nodes.type',
            FieldType::CHILDREN_T->value => 'children-nodes.type',
            FieldType::COLOUR_T->value => 'colour.type',
            FieldType::GEOTAG_T->value => 'geographic.coordinates.type',
            FieldType::CUSTOM_FORMS_T->value => 'custom-forms.type',
            FieldType::MULTI_GEOTAG_T->value => 'multiple.geographic.coordinates.type',
            FieldType::JSON_T->value => 'json.type',
            FieldType::CSS_T->value => 'css.type',
            FieldType::COUNTRY_T->value => 'country.type',
            FieldType::YAML_T->value => 'yaml.type',
            FieldType::MANY_TO_MANY_T->value => 'many-to-many.type',
            FieldType::MANY_TO_ONE_T->value => 'many-to-one.type',
            FieldType::SINGLE_PROVIDER_T->value => 'single-provider.type',
            FieldType::MULTI_PROVIDER_T->value => 'multiple-provider.type',
            FieldType::COLLECTION_T->value => 'collection.type',
        ];
    }

    protected static function toDoctrine(): array
    {
        return [
            FieldType::STRING_T->value => 'string',
            FieldType::DATETIME_T->value => 'datetime',
            FieldType::DATE_T->value => 'datetime',
            FieldType::RICHTEXT_T->value => 'text',
            FieldType::TEXT_T->value => 'text',
            FieldType::MARKDOWN_T->value => 'text',
            FieldType::BOOLEAN_T->value => 'boolean',
            FieldType::INTEGER_T->value => 'integer',
            FieldType::DECIMAL_T->value => 'decimal',
            FieldType::EMAIL_T->value => 'string',
            FieldType::ENUM_T->value => 'string',
            FieldType::MULTIPLE_T->value => 'json',
            FieldType::DOCUMENTS_T->value => null,
            FieldType::NODES_T->value => null,
            FieldType::CHILDREN_T->value => null,
            FieldType::COLOUR_T->value => 'string',
            FieldType::GEOTAG_T->value => 'json',
            FieldType::CUSTOM_FORMS_T->value => null,
            FieldType::MULTI_GEOTAG_T->value => 'json',
            FieldType::JSON_T->value => 'text',
            FieldType::CSS_T->value => 'text',
            FieldType::COUNTRY_T->value => 'string',
            FieldType::YAML_T->value => 'text',
            FieldType::MANY_TO_MANY_T->value => null,
            FieldType::MANY_TO_ONE_T->value => null,
            FieldType::SINGLE_PROVIDER_T->value => 'string',
            FieldType::MULTI_PROVIDER_T->value => 'json',
            FieldType::COLLECTION_T->value => 'json',
        ];
    }

    public static function fromHuman(string $type): FieldType
    {
        if (!str_ends_with('.type', $type)) {
            $type = $type.'.type';
        }
        $results = array_search($type, self::toHuman(), true);
        if (false === $results) {
            throw new \InvalidArgumentException(sprintf('The type %s is not a valid field type.', $type));
        }

        return self::tryFrom($results);
    }
}
