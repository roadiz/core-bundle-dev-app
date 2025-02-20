<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use RZ\Roadiz\CoreBundle\Enum\FieldType;

trait FieldTypeTrait
{
    public function getType(): FieldType
    {
        return $this->type;
    }

    /**
     * Backward compatible setType to accept old int values along FieldSet enum.
     *
     * @return $this
     */
    public function setType(int|FieldType $type): self
    {
        $this->type = is_int($type) ? FieldType::tryFrom($type) : $type;

        return $this;
    }

    public function getTypeName(): string
    {
        return $this->getType()->toHuman();
    }

    public function getDoctrineType(): string
    {
        return $this->getType()->toDoctrine();
    }

    /**
     * @return bool Is node type field virtual, it's just an association, no doctrine field created
     */
    public function isVirtual(): bool
    {
        return null === $this->getType()->toDoctrine();
    }

    /**
     * @return bool Is node type field searchable
     */
    public function isSearchable(): bool
    {
        return in_array($this->getType(), FieldType::searchableTypes());
    }

    public function isString(): bool
    {
        return FieldType::STRING_T === $this->getType();
    }

    public function isText(): bool
    {
        return FieldType::TEXT_T === $this->getType();
    }

    public function isDate(): bool
    {
        return FieldType::DATE_T === $this->getType();
    }

    public function isDateTime(): bool
    {
        return FieldType::DATETIME_T === $this->getType();
    }

    public function isRichText(): bool
    {
        return FieldType::RICHTEXT_T === $this->getType();
    }

    public function isMarkdown(): bool
    {
        return FieldType::MARKDOWN_T === $this->getType();
    }

    public function isBool(): bool
    {
        return $this->isBoolean();
    }

    public function isBoolean(): bool
    {
        return FieldType::BOOLEAN_T === $this->getType();
    }

    public function isInteger(): bool
    {
        return FieldType::INTEGER_T === $this->getType();
    }

    public function isDecimal(): bool
    {
        return FieldType::DECIMAL_T === $this->getType();
    }

    public function isEmail(): bool
    {
        return FieldType::EMAIL_T === $this->getType();
    }

    public function isDocuments(): bool
    {
        return FieldType::DOCUMENTS_T === $this->getType();
    }

    public function isPassword(): bool
    {
        return FieldType::PASSWORD_T === $this->getType();
    }

    public function isColor(): bool
    {
        return $this->isColour();
    }

    public function isColour(): bool
    {
        return FieldType::COLOUR_T === $this->getType();
    }

    public function isGeoTag(): bool
    {
        return FieldType::GEOTAG_T === $this->getType();
    }

    public function isNodes(): bool
    {
        return FieldType::NODES_T === $this->getType();
    }

    public function isUser(): bool
    {
        return FieldType::USER_T === $this->getType();
    }

    public function isEnum(): bool
    {
        return FieldType::ENUM_T === $this->getType();
    }

    public function isChildrenNodes(): bool
    {
        return FieldType::CHILDREN_T === $this->getType();
    }

    public function isCustomForms(): bool
    {
        return FieldType::CUSTOM_FORMS_T === $this->getType();
    }

    public function isMultiple(): bool
    {
        return FieldType::MULTIPLE_T === $this->getType();
    }

    public function isMultiGeoTag(): bool
    {
        return FieldType::MULTI_GEOTAG_T === $this->getType();
    }

    public function isJson(): bool
    {
        return FieldType::JSON_T === $this->getType();
    }

    public function isYaml(): bool
    {
        return FieldType::YAML_T === $this->getType();
    }

    public function isCss(): bool
    {
        return FieldType::CSS_T === $this->getType();
    }

    public function isManyToMany(): bool
    {
        return FieldType::MANY_TO_MANY_T === $this->getType();
    }

    public function isManyToOne(): bool
    {
        return FieldType::MANY_TO_ONE_T === $this->getType();
    }

    public function isCountry(): bool
    {
        return FieldType::COUNTRY_T === $this->getType();
    }

    public function isSingleProvider(): bool
    {
        return FieldType::SINGLE_PROVIDER_T === $this->getType();
    }

    public function isMultipleProvider(): bool
    {
        return $this->isMultiProvider();
    }

    public function isMultiProvider(): bool
    {
        return FieldType::MULTI_PROVIDER_T === $this->getType();
    }

    public function isCollection(): bool
    {
        return FieldType::COLLECTION_T === $this->getType();
    }
}
