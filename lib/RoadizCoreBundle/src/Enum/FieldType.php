<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Enum;

enum FieldType: int
{
    case BOOLEAN_T = 5;
    case CHECK_GROUP_T = 20;
    case CHILDREN_T = 16;
    case COLLECTION_T = 31;
    case COLOUR_T = 11;
    case COUNTRY_T = 25;
    case CSS_T = 24;
    case CUSTOM_FORMS_T = 17;
    case DATETIME_T = 1;
    case DATE_T = 22;
    case DECIMAL_T = 7;
    case DOCUMENTS_T = 9;
    case EMAIL_T = 8;
    case ENUM_T = 15;
    case GEOTAG_T = 12;
    case INTEGER_T = 6;
    case JSON_T = 23;
    case MANY_TO_MANY_T = 27;
    case MANY_TO_ONE_T = 28;
    case MARKDOWN_T = 4;
    case MULTIPLE_T = 18;
    case MULTI_GEOTAG_T = 21;
    case MULTI_PROVIDER_T = 29;
    case NODES_T = 13;
    case PASSWORD_T = 10;
    case RADIO_GROUP_T = 19;
    case RICHTEXT_T = 3;
    case SINGLE_PROVIDER_T = 30;
    case STRING_T = 0;
    case TEXT_T = 2;
    case USER_T = 14;
    case YAML_T = 26;

    public function toHuman(): string
    {
        return self::humanValues()[$this->value];
    }

    /**
     * @return array<int, string>
     */
    public static function humanValues(): array
    {
        return [
            FieldType::BOOLEAN_T->value => 'boolean.type',
            FieldType::CHILDREN_T->value => 'children-nodes.type',
            FieldType::COLLECTION_T->value => 'collection.type',
            FieldType::COLOUR_T->value => 'colour.type',
            FieldType::COUNTRY_T->value => 'country.type',
            FieldType::CSS_T->value => 'css.type',
            FieldType::CUSTOM_FORMS_T->value => 'custom-forms.type',
            FieldType::DATETIME_T->value => 'date-time.type',
            FieldType::DATE_T->value => 'date.type',
            FieldType::DECIMAL_T->value => 'decimal.type',
            FieldType::DOCUMENTS_T->value => 'documents.type',
            FieldType::EMAIL_T->value => 'email.type',
            FieldType::ENUM_T->value => 'single-choice.type',
            FieldType::GEOTAG_T->value => 'geographic.coordinates.type',
            FieldType::INTEGER_T->value => 'integer.type',
            FieldType::JSON_T->value => 'json.type',
            FieldType::MANY_TO_MANY_T->value => 'many-to-many.type',
            FieldType::MANY_TO_ONE_T->value => 'many-to-one.type',
            FieldType::MARKDOWN_T->value => 'markdown.type',
            FieldType::MULTIPLE_T->value => 'multiple-choice.type',
            FieldType::MULTI_GEOTAG_T->value => 'multiple.geographic.coordinates.type',
            FieldType::MULTI_PROVIDER_T->value => 'multiple-provider.type',
            FieldType::NODES_T->value => 'nodes.type',
            FieldType::SINGLE_PROVIDER_T->value => 'single-provider.type',
            FieldType::STRING_T->value => 'string.type',
            FieldType::TEXT_T->value => 'text.type',
            FieldType::YAML_T->value => 'yaml.type',
        ];
    }

    public static function doctrineValues(): array
    {
        return [
            FieldType::BOOLEAN_T->value => 'boolean',
            FieldType::CHILDREN_T->value => null,
            FieldType::COLLECTION_T->value => 'json',
            FieldType::COLOUR_T->value => 'string',
            FieldType::COUNTRY_T->value => 'string',
            FieldType::CSS_T->value => 'text',
            FieldType::CUSTOM_FORMS_T->value => null,
            FieldType::DATETIME_T->value => 'datetime',
            FieldType::DATE_T->value => 'datetime',
            FieldType::DECIMAL_T->value => 'decimal',
            FieldType::DOCUMENTS_T->value => null,
            FieldType::EMAIL_T->value => 'string',
            FieldType::ENUM_T->value => 'string',
            FieldType::GEOTAG_T->value => 'json',
            FieldType::INTEGER_T->value => 'integer',
            FieldType::JSON_T->value => 'text',
            FieldType::MANY_TO_MANY_T->value => null,
            FieldType::MANY_TO_ONE_T->value => null,
            FieldType::MARKDOWN_T->value => 'text',
            FieldType::MULTIPLE_T->value => 'json',
            FieldType::MULTI_GEOTAG_T->value => 'json',
            FieldType::MULTI_PROVIDER_T->value => 'json',
            FieldType::NODES_T->value => null,
            FieldType::RICHTEXT_T->value => 'text',
            FieldType::SINGLE_PROVIDER_T->value => 'string',
            FieldType::STRING_T->value => 'string',
            FieldType::TEXT_T->value => 'text',
            FieldType::YAML_T->value => 'text',
        ];
    }

    public static function searchableTypes(): array
    {
        return [
            FieldType::STRING_T->value,
            FieldType::RICHTEXT_T->value,
            FieldType::TEXT_T->value,
            FieldType::MARKDOWN_T->value,
        ];
    }

    public static function fromHuman(string $type): FieldType
    {
        if (!str_ends_with('.type', $type)) {
            $type = $type.'.type';
        }
        $results = array_search($type, self::humanValues(), true);
        if (false === $results) {
            throw new \InvalidArgumentException(sprintf('The type %s is not a valid field type.', $type));
        }

        return self::tryFrom($results);
    }
}
