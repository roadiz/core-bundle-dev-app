<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\Constraint;

use RZ\Roadiz\Utils\StringHandler;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class NodeSourceReservedName extends Constraint
{
    /**
     * Field names that collide with a getter or setter already declared on
     * RZ\Roadiz\CoreBundle\Entity\NodesSources.
     *
     * A node-type field getter is named `get`.camelCase(name); when that name
     * matches a base method the generated node-source entity fails to compile
     * ("Cannot override final method" for metaTitle/metaDescription, or an
     * incompatible return type for the others). Reserving the names surfaces
     * the problem as a validation error instead of a fatal error at generation
     * time.
     *
     * Names are listed here in camelCase for readability; comparison is done on
     * the variablized form (see isReserved()), so both "metaTitle" and
     * "meta_title" match.
     *
     * @var array<string>
     */
    public static array $reservedNames = [
        'documentsByFields',
        'documentsByFieldsWithField',
        'documentsByFieldsWithName',
        'id',
        'identifier',
        'listingSortOptions',
        'metaDescription',
        'metaDescriptionOrFallback',
        'metaTitle',
        'noIndex',
        'node',
        'nodeTypeColor',
        'nodeTypeName',
        'nodesSourcesDocuments',
        'oneDisplayableDocument',
        'parent',
        'publishable',
        'published',
        'publishedAt',
        'reachable',
        'redirections',
        'shareImage',
        'title',
        'translation',
        'urlAliases',
    ];

    public string $message = 'node_type_field.name.%name%.is.reserved.for.node.source';

    /**
     * Tells if a node-type field name would collide with a base NodesSources
     * method, comparing on the variablized form so both camelCase and
     * snake_case spellings are caught.
     */
    public static function isReserved(string $name): bool
    {
        if ('' === $name) {
            return false;
        }

        $variablizedName = StringHandler::variablize($name);
        foreach (self::$reservedNames as $reservedName) {
            if (StringHandler::variablize($reservedName) === $variablizedName) {
                return true;
            }
        }

        return false;
    }
}
