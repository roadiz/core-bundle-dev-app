---
title: Node-type Decorators
---

# Node type Decorators

Roadiz allows you to customize any node-types and node-type fields **non-structural** properties. This can be handy if you need to change node-types appearence (display names, colors) in Back-office without needing to update your project configuration or emptying caches.

## Entity representation

This decoration is represented by the NodeTypeDecorator entity, which
contains three fields :

- `path`
- `property`
- `value`

## `path` property :

This property is used to define the *node type* or *node type field* we want to customize.
It consists of the `node type name` and the `node type field name` separated by a `dot`.

::: tip
Exemple of `path` for the content field of a Page :

`Page.content`
:::

::: warning
For decorate an property of a node type simply leave empty after the dot :

`Page.`
:::

## `property` property :

This property is used to define the *node type property* or *node type field property* we want to customize.

It consists of a ``Enum`` which depends on the path containing a field or not.

**List of the property for node type :**
1.  displayName
2.  description
3.  color

**List of the property for node type field :**
1.  field_label
2.  field_universal
3.  field_description
4.  field_placeholder
5.  field_visible
6.  field_min_length
7.  field_max_length

::: tip
Exemple of `property` for the content field of a Page :

`field_label`
:::

::: warning
You can't attribute a `node type` property to a `path` with field
(exemple: path = `Page.` and property = `field_label`)

You can't attribute a `node type field` property to a `path` without
field (exemple: path = `Page.content` and property = `displayName`)
:::

## `value` property :

This property is used to define the value who override default.

It consists of a `string` linked to its property type.

| Property          | Type              |
|-------------------|-------------------|
| displayName       | text              |
| description       | text              |
| color             | hexadecimal color |
| field_label       | text              |
| field_universal   | boolean           |
| field_description | text              |
| field_placeholder | text              |
| field_visible     | boolean           |
| field_min_length  | integer           |
| field_max_length  | integer           |

::: tip
Exemple of `value` on the `field_label` property : `'A text Label'`

Exemple of `value` on the `field_visible` property : `'true'`

Exemple of `value` on the `color` property : `'#FF1185'`

Exemple of `value` on the `field_max_length` property : `'15'`
:::
