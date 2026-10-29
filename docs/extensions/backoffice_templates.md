# Backoffice Templates System

Roadiz provides a modular template system for building custom back-office pages. This system is based on reusable templates, Twig macros, and embeddable widgets that ensure visual consistency across the admin interface.

## Overview

The template system is organized into several layers:

| Layer | Namespace | Purpose |
|-------|-----------|---------|
| **Generic templates** | `@RoadizRozier/admin/` | Base layouts for common page types |
| **Macros** | `@RoadizRozier/macros/` | Reusable UI components (buttons, badges, etc.) |
| **Widgets** | `@RoadizRozier/widgets/` | Complex reusable blocks (filters, bulk actions) |
| **Includes** | `@RoadizRozier/includes/` | Table structures, meta displays |

## Generic Admin Templates

### `admin/base.html.twig`

The base layout for listing pages. Extends `@RoadizRozier/layout.html.twig` and provides a structured content area with bulk selection support.

**Available blocks:**

| Block | Purpose |
|-------|---------|
| `header` | Page header with title, breadcrumb, and action buttons |
| `content_filters` | Search, filters, and bulk action buttons |
| `content_body` | Main content area (tables, cards, etc.) |

**Example usage:**

```twig
{% extends "@RoadizRozier/admin/base.html.twig" %}

{%- block header -%}
    {% include '@RoadizRozier/admin/head.html.twig' with {
        title: 'my_entities'|trans,
        filters: filters,
        buttons: [{
            label: 'add.entity'|trans,
            attributes: { href: path('my_entity_add') },
            icon: 'rz-icon-ri--add-line',
        }]
    } only %}
{%- endblock -%}

{%- block content_filters -%}
    {% include "@RoadizRozier/widgets/rz_filters_bar.html.twig" with {
        filters: filters,
        display_select_all_button: true,
    } only %}
{%- endblock -%}

{%- block content_body -%}
    <article>
        {# Your table or content here #}
    </article>
{%- endblock -%}
```

### `admin/head.html.twig`

A generic and reusable header template for admin pages. It handles page titles, breadcrumbs, action buttons, item counts, and translation switching.

**Variables:**

| Variable | Type | Required | Description |
|----------|------|----------|-------------|
| `title` | string | Yes | Page title |
| `subtitle` | string | No | Optional subtitle |
| `buttons` | array | No | Array of button configurations |
| `filters` | object | No | Filter object with `itemCount` property |
| `breadcrumb` | object | No | Breadcrumb configuration |
| `available_translations` | array | No | Available translations for switching |
| `translation` | object | No | Current translation |
| `translation_route` | string | No | Route for translation switching |
| `translation_params` | array | No | Route parameters |
| `with_nav_bar` | bool | No | Show navigation bar |
| `nav_bar_template` | string | No | Path to nav bar template |
| `nav_bar_context` | array | No | Context for nav bar |

**Button configuration:**

```twig
{
    label: 'button.label'|trans,
    icon: 'rz-icon-ri--add-line',
    attributes: {
        href: path('route_name'),
        title: 'tooltip'|trans,
    }
}
```

**Breadcrumb configuration:**

```twig
{
    label: 'breadcrumb-aria-label',
    parent: [
        {
            label: 'parent.page'|trans,
            type: 'listing',  {# Optional: adds list icon #}
            url: path('parent_route'),
        }
    ],
    current: 'current.page'|trans,
}
```

**Full example:**

```twig
{% include '@RoadizRozier/admin/head.html.twig' with {
    title: 'edit.article'|trans ~ ' "' ~ item.title ~ '"',
    filters: filters,
    buttons: [
        {
            label: 'add.article'|trans,
            attributes: { href: path('article_add') },
            icon: 'rz-icon-ri--add-line',
        }
    ],
    breadcrumb: {
        label: 'article-breadcrumb',
        parent: [{
            label: 'manage.articles'|trans,
            type: 'listing',
            url: path('article_list'),
        }],
        current: item.title,
    },
    available_translations: translations,
    translation: currentTranslation,
    translation_route: 'article_edit',
    translation_params: { id: item.id },
} only %}
```

### `admin/confirm_action.html.twig`

Template for confirmation pages (delete, bulk actions). Displays a warning message, affected items preview, and confirm/cancel buttons.

**Variables:**

| Variable | Type | Required | Description |
|----------|------|----------|-------------|
| `title` | string | Yes | Page title |
| `form` | Form | Yes | Symfony form object |
| `alertMessage` | string | Yes | Warning message (translation key) |
| `cancelPath` | string | Yes | URL for cancel button |
| `items` | array | No | Items to display as preview cards |
| `headPath` | string | No | Custom header template path |
| `messageType` | string | No | Message style: `error`, `warning`, `info` |
| `action_label` | string | No | Submit button label (default: `delete`) |
| `action_icon` | string | No | Submit button icon |
| `action_color` | string | No | Submit button color (default: `danger`) |

**Example usage in controller:**

```php
return $this->render('@RoadizRozier/admin/confirm_action.html.twig', [
    'title' => $this->translator->trans('delete.article'),
    'form' => $form->createView(),
    'alertMessage' => 'are.you.sure.delete.article',
    'cancelPath' => $this->generateUrl('article_edit', ['id' => $article->getId()]),
    'items' => [$article],
    'headPath' => 'admin/article/head.html.twig',
]);
```

### Items Display System

The `items` variable displays preview cards for entities affected by the action. The template supports two modes:

#### Mode 1: Passing Doctrine Entities Directly

Pass an array of Doctrine entities and let the template convert them automatically:

```php
return $this->render('@RoadizRozier/admin/confirm_action.html.twig', [
    // ...
    'items' => $documents,  // Array of Document entities
]);
```

The template detects entities and calls `getExplorerItem()` to convert them to display arrays.

#### Mode 2: Passing Pre-formatted Arrays

For custom entities or fine-grained control, use `ExplorerItemFactoryInterface`:

```php
use RZ\Roadiz\CoreBundle\Explorer\ExplorerItemFactoryInterface;

public function __construct(
    private readonly ExplorerItemFactoryInterface $explorerItemFactory,
) {}

public function deleteAction(): Response
{
    $items = [];
    foreach ($entities as $entity) {
        $items[] = $this->explorerItemFactory->createForEntity($entity, [
            'classname' => 'My Entity Type',
            'displayable' => 'getName',  // Method to call for title
        ])->toArray();
    }

    return $this->render('@RoadizRozier/admin/confirm_action.html.twig', [
        // ...
        'items' => $items,
    ]);
}
```

### ExplorerItem Structure

Each item array contains the following fields:

| Field | Type | Description |
|-------|------|-------------|
| `id` | `string\|int` | Entity identifier |
| `displayable` | `string` | Main display text (title, name) |
| `classname` | `string` | Secondary text (entity type, category) |
| `editItem` | `?string` | URL to edit the entity |
| `thumbnail` | `?Document` | Document for thumbnail display |
| `published` | `bool` | Publication state |
| `color` | `?string` | Associated color (e.g., image average color) |

**Additional fields for Documents:**

| Field | Type | Description |
|-------|------|-------------|
| `isImage` | `bool` | Is an image file |
| `isVideo` | `bool` | Is a video file |
| `isPdf` | `bool` | Is a PDF file |
| `isPrivate` | `bool` | Is private (no public URL) |
| `thumbnail80` | `?string` | Pre-generated 80px thumbnail URL |
| `pictureUrl` | `?string` | External image URL (avatars) |

**Additional fields for Users:**

| Field | Type | Description |
|-------|------|-------------|
| `pictureUrl` | `?string` | User profile picture URL |

### Supported Entity Types

`ExplorerItemFactory` automatically creates the appropriate explorer item for these entities:

| Entity | Explorer Item Class |
|--------|---------------------|
| `DocumentInterface` | `DocumentExplorerItem` |
| `Node` | `NodeExplorerItem` |
| `NodesSources` | `NodeSourceExplorerItem` |
| `Tag` | `TagExplorerItem` |
| `Folder` | `FolderExplorerItem` |
| `User` | `UserExplorerItem` |
| `Setting` | `SettingExplorerItem` |
| `NodeType` | `NodeTypeExplorerItem` |
| `Translation` | `TranslationExplorerItem` |
| `CustomForm` | `CustomFormExplorerItem` |
| Other `PersistableInterface` | `ConfigurableExplorerItem` |

### ConfigurableExplorerItem for Custom Entities

For entities not in the list above, use `ConfigurableExplorerItem` with a configuration array:

```php
$explorerItem = $this->explorerItemFactory->createForEntity($myEntity, [
    'classname' => 'Product',              // Displayed as subtitle
    'displayable' => 'getName',             // Method returning the title
    'alt_displayable' => 'getCreatedAt',    // Method for alternative text
    'thumbnail' => 'getImage',              // Method returning a Document
]);
```

| Configuration | Type | Description |
|---------------|------|-------------|
| `classname` | `string` | Static text for entity type |
| `displayable` | `string` | Method name returning the main display text |
| `alt_displayable` | `string` | Method name for alternative/secondary text |
| `thumbnail` | `string` | Method name returning a `Document` or `Collection<Document>` |

::: tip
The `displayable` and `thumbnail` methods are called dynamically. If `thumbnail` returns a Collection, the first Document is used.
:::

### Card Rendering

Items are rendered using the `rz_card.html.twig` macro which supports three thumbnail modes:

1. **`thumbnail`**: A `DocumentInterface` rendered via the `display` filter
2. **`pictureUrl`**: An external URL (for avatars, external images)
3. **`thumbnail80`**: A pre-generated thumbnail URL string

```twig
{# The template handles this automatically #}
{% if hasThumbnail %}
    {{ item.thumbnail|display(format) }}
{% elseif hasPictureUrl %}
    <img src="{{ item.pictureUrl }}" alt="{{ item.displayable }}">
{% elseif hasThumbnail80 %}
    <img src="{{ item.thumbnail80 }}" alt="{{ item.displayable }}">
{% endif %}
```

## Macros System

Macros are reusable Twig components for rendering UI elements consistently.

### `rz_button.html.twig`

Renders buttons and button-like links.

**Import and usage:**

```twig
{% import "@RoadizRozier/macros/rz_button.html.twig" as rz_button %}

{# Basic submit button #}
{{ rz_button.root({
    attributes: { type: 'submit' },
    label: 'save',
    emphasis: 'primary',
    icon: 'rz-icon-ri--save-line',
}) }}

{# Link styled as button #}
{{ rz_button.root({
    attributes: { href: path('my_route') },
    label: 'go.to.page',
    emphasis: 'secondary',
}) }}

{# Danger button #}
{{ rz_button.root({
    attributes: { type: 'button' },
    label: 'delete',
    emphasis: 'primary',
    color: 'danger',
    icon: 'rz-icon-ri--delete-bin-7-line',
}) }}
```

**Options:**

| Option | Type | Description |
|--------|------|-------------|
| `label` | string | Button text (auto-translated) |
| `icon` | string | Icon class |
| `emphasis` | string | `primary`, `secondary`, `tertiary` |
| `color` | string | `danger`, `success`, `warning` |
| `size` | string | `sm`, `md`, `xs` |
| `attributes` | object | HTML attributes (`href`, `type`, `title`, etc.) |
| `tagName` | string | HTML tag (default: `button`, auto `a` if `href`) |

### `rz_badge.html.twig`

Renders badges for status indicators, counts, or labels.

```twig
{% import "@RoadizRozier/macros/rz_badge.html.twig" as rz_badge %}

{{ rz_badge.item({
    label: 'published',
    icon: 'rz-icon-ri--check-line',
    size: 'sm',
    color: 'success',
}) }}

{# Badge as link #}
{{ rz_badge.item({
    label: tag.name,
    attributes: { href: path('tag_edit', { id: tag.id }) },
}) }}
```

**Options:**

| Option | Type | Description |
|--------|------|-------------|
| `label` | string | Badge text |
| `icon` | string | Icon class |
| `size` | string | `xs`, `sm` |
| `color` | string | `success`, `danger`, `warning`, `default` |
| `attributes` | object | HTML attributes (use `href` for link badge) |

### `rz_actions_menu.html.twig`

An embeddable template for action menus (typically positioned on the side of edit pages).

```twig
{% embed "@RoadizRozier/macros/rz_actions_menu.html.twig" with {
    vertical: true,
    class: 'actions-menu',
} only %}
    {% block items %}
        {{ _self.saveItem('#my-form-id') }}
        {{ _self.deleteItem(href: path('entity_delete', { id: item.id })) }}
    {% endblock %}
{% endembed %}
```

**Built-in macros:**

| Macro | Description |
|-------|-------------|
| `saveItem(dataActionSave, label)` | Save button bound to a form |
| `deleteItem(label, href, attr)` | Delete button/link |
| `item(label, class, href, icon, attr)` | Generic action item |

### `rz_dropdown_menu.html.twig`

Creates dropdown menus with customizable items.

```twig
{% embed "@RoadizRozier/macros/rz_dropdown_menu.html.twig" with {
    targetId: 'my-dropdown',
    title: 'actions'|trans,
} %}
    {% block items %}
        {{ _self.item({
            label: 'edit',
            href: path('edit_route'),
            leftIcon: 'rz-icon-ri--edit-line',
        }) }}
        {{ _self.item({
            label: 'delete',
            href: path('delete_route'),
            leftIcon: 'rz-icon-ri--delete-bin-7-line',
        }) }}
    {% endblock %}
{% endembed %}
```

**Item options:**

| Option | Type | Description |
|--------|------|-------------|
| `label` | string | Item text |
| `description` | string | Secondary text |
| `href` | string | Link URL |
| `leftIcon` | string | Icon on the left |
| `rightIcon` | string | Icon on the right |
| `badgeLabel` | string | Badge text |
| `selected` | bool | Highlight as selected |

### `rz_link.html.twig`

Simple link component.

```twig
{% import '@RoadizRozier/macros/rz_link.html.twig' as rz_link %}

{{ rz_link.root({
    label: 'see.details',
    icon: 'rz-icon-ri--arrow-right-line',
    attributes: { href: path('detail_route') },
}) }}
```

### `rz_bulk.html.twig`

Utilities for bulk selection in tables.

```twig
{% import '@RoadizRozier/macros/rz_bulk.html.twig' as rz_bulk %}

{# In table header #}
{{ rz_bulk.select_all_input() }}

{# In table row #}
{{ rz_bulk.select_input({ value: item.id }) }}
```

## Reusable Widgets

### `widgets/rz_filters_bar.html.twig`

Complete filter bar with search, pagination, and bulk selection controls.

```twig
{% include "@RoadizRozier/widgets/rz_filters_bar.html.twig" with {
    filters: filters,
    display_select_all_button: true,
    display_layout_fields: false,
} only %}
```

**With custom filters using embed:**

```twig
{% embed "@RoadizRozier/widgets/rz_filters_bar.html.twig" with {
    filters: filters,
    display_select_all_button: true,
} only %}
    {% block before_submit %}
        <select name="status" is="rz-select">
            <option value="">{{ 'all.statuses'|trans }}</option>
            <option value="published">{{ 'published'|trans }}</option>
            <option value="draft">{{ 'draft'|trans }}</option>
        </select>
    {% endblock %}
{% endembed %}
```

**Variables:**

| Variable | Type | Description |
|----------|------|-------------|
| `filters` | object | Filter state with `search`, `itemCount`, `pageCount`, etc. |
| `display_select_all_button` | bool | Show "Select All" button |
| `display_layout_fields` | bool | Show grid/list toggle |
| `with_filter_form` | bool | Enable search form (default: true) |

### `widgets/rz_bulk_actions.html.twig`

Floating action bar that appears when items are selected.

```twig
{% embed "@RoadizRozier/widgets/rz_bulk_actions.html.twig" %}
    {% import "@RoadizRozier/macros/rz_button.html.twig" as rz_button %}
    
    {% block other_actions %}
        {{ rz_button.root({
            attributes: { type: 'submit', form: 'bulk-publish-form' },
            emphasis: 'primary',
            color: 'success',
            icon: 'rz-icon-ri--check-line',
        }) }}
    {% endblock %}
    
    {% block delete_form %}
        <form action="{{ path('bulk_delete') }}" method="POST">
            {{ rz_button.root({
                attributes: { type: 'submit' },
                color: 'danger',
                emphasis: 'primary',
                icon: 'rz-icon-ri--delete-bin-7-line',
            }) }}
        </form>
    {% endblock %}
{% endembed %}
```

::: tip
For standard bulk actions (publish, unpublish, delete), use the pre-built `@RoadizRozier/admin/bulk_actions.html.twig` which handles form integration automatically:

```twig
{% include '@RoadizRozier/admin/bulk_actions.html.twig' %}
```

This requires `hasBulkActions`, `bulkPublishForm`, `bulkUnpublishForm`, and `bulkDeleteForm` variables from your controller.
:::

### `includes/rz_table.html.twig`

Embeddable table component with sorting, selection, and action columns.

```twig
{% embed '@RoadizRozier/includes/rz_table.html.twig' with {
    selection_available: true,
    actions_available: true,
    items: items,
} only %}
    {% block thead_cells %}
        <th>{{ 'title'|trans }}</th>
        <th>{{ 'created_at'|trans }}</th>
        <th>{{ 'status'|trans }}</th>
    {% endblock %}
    
    {% block tbody_content %}
        {% for item in items %}
            <tr>
                {% if selection_available %}
                    <td>
                        {% import '@RoadizRozier/macros/rz_bulk.html.twig' as rz_bulk %}
                        {{ rz_bulk.select_input({ value: item.id }) }}
                    </td>
                {% endif %}
                <td>{{ item.title }}</td>
                <td>{{ item.createdAt|format_datetime('short', 'short') }}</td>
                <td>
                    {% import "@RoadizRozier/macros/rz_badge.html.twig" as rz_badge %}
                    {{ rz_badge.item({ label: item.status }) }}
                </td>
                {% if actions_available %}
                    <td>
                        {% include '@RoadizRozier/includes/rz_table_actions.html.twig' with {
                            edit_href: path('entity_edit', { id: item.id }),
                            delete_href: path('entity_delete', { id: item.id }),
                        } %}
                    </td>
                {% endif %}
            </tr>
        {% else %}
            <tr>
                <td colspan="5">{{ 'no.items'|trans }}</td>
            </tr>
        {% endfor %}
    {% endblock %}
{% endembed %}
```

**Variables:**

| Variable | Type | Description |
|----------|------|-------------|
| `selection_available` | bool | Add checkbox column |
| `actions_available` | bool | Add actions column header |
| `items` | array | Items to iterate |
| `sortable.enabled` | bool | Enable drag-and-drop sorting |
| `sortable.url` | string | URL for sort updates |

### `includes/rz_table_actions.html.twig`

Row action buttons (edit, delete, custom).

```twig
{% embed '@RoadizRozier/includes/rz_table_actions.html.twig' with {
    edit_href: path('entity_edit', { id: item.id }),
    delete_href: path('entity_delete', { id: item.id }),
} %}
    {% block other_actions %}
        {{ _self.button({
            icon: 'rz-icon-ri--eye-line',
            attributes: {
                href: path('entity_preview', { id: item.id }),
                title: 'preview'|trans,
            }
        }) }}
    {% endblock %}
{% endembed %}
```

## Embed vs Include Pattern

Understanding when to use `{% include %}` versus `{% embed %}` is essential for working with Roadiz templates.

### Use `{% include %}` when:

- You pass all data through variables
- You don't need to modify any blocks
- The template is self-contained

```twig
{# Simple inclusion with variables #}
{% include '@RoadizRozier/admin/head.html.twig' with {
    title: 'My Page',
    buttons: [...]
} only %}
```

### Use `{% embed %}` when:

- You need to override or extend blocks
- You want to inject custom content into predefined slots
- The template provides extension points

```twig
{# Embedding with block customization #}
{% embed "@RoadizRozier/widgets/rz_filters_bar.html.twig" with {
    filters: filters,
} only %}
    {% block before_submit %}
        {# Custom filter added here #}
        <select name="category">...</select>
    {% endblock %}
{% endembed %}
```

### Key differences:

| Aspect | `include` | `embed` |
|--------|-----------|---------|
| Block override | No | Yes |
| Syntax | Single tag | Opening/closing tags |
| Use case | Static templates | Extensible templates |
| `_self` access | No | Yes (access parent macros) |

## Overriding Templates

You can override any Rozier template in your project to customize the back-office appearance.

### Directory Structure

Place overridden templates in `templates/bundles/RoadizRozierBundle/`:

```
templates/
└── bundles/
    └── RoadizRozierBundle/
        ├── dashboard/
        │   └── index.html.twig      # Override dashboard
        ├── admin/
        │   └── head.html.twig       # Override page header
        └── redirections/
            └── list.html.twig       # Override redirections listing
```

### Extending Original Templates

Use the `@!` prefix to extend the original template while adding customizations:

```twig
{# templates/bundles/RoadizRozierBundle/dashboard/index.html.twig #}
{% extends "@!RoadizRozier/dashboard/index.html.twig" %}
{% import '@RoadizRozier/macros/rz_button.html.twig' as rz_button %}

{%- block header -%}
    {% include '@RoadizRozier/admin/head.html.twig' with {
        title: "hello.%name%"|trans({'%name%': displayName}),
        breadcrumb: {
            label: 'breadcrumb',
            current: 'dashboard'|trans,
        },
        buttons: [
            {
                label: 'quick.action'|trans,
                attributes: { href: path('my_custom_route') },
                icon: 'rz-icon-ri--add-line',
            }
        ],
    } only %}
{%- endblock -%}

{%- block dashboard_panels -%}
    {{ parent() }}
    
    {# Add custom panel #}
    {% embed '@RoadizRozier/dashboard/panel.html.twig' with {
        icon: 'rz-icon-ri--bar-chart-line',
        title: 'my.custom.stats'|trans,
    } %}
        {% block content %}
            <div class="text-h1-md">{{ customStats }}</div>
        {% endblock %}
    {% endembed %}
{%- endblock -%}
```

### Complete Override

For complete replacement without inheritance:

```twig
{# templates/bundles/RoadizRozierBundle/redirections/list.html.twig #}
{% extends '@RoadizRozier/admin/base.html.twig' %}

{# Completely custom implementation #}
{%- block header -%}
    {# Your custom header #}
{%- endblock -%}

{%- block content_body -%}
    {# Your custom content #}
{%- endblock -%}
```

## Icons Reference

Roadiz uses [Remix Icon](https://remixicon.com/) as its icon library.

### Icon Classes

| Pattern | Example | Description |
|---------|---------|-------------|
| `rz-icon-ri--{name}` | `rz-icon-ri--add-line` | Remix Icon |
| `rz-icon-rz--{name}` | `rz-icon-rz--status-draft-line` | Roadiz custom icons |

### Commonly Used Icons

| Icon Class | Usage |
|------------|-------|
| `rz-icon-ri--add-line` | Add/Create actions |
| `rz-icon-ri--edit-line` | Edit actions |
| `rz-icon-ri--delete-bin-7-line` | Delete actions |
| `rz-icon-ri--save-line` | Save actions |
| `rz-icon-ri--search-line` | Search |
| `rz-icon-ri--check-line` | Confirm/Publish |
| `rz-icon-ri--close-line` | Cancel/Unpublish |
| `rz-icon-ri--arrow-right-line` | Navigation |
| `rz-icon-ri--eye-line` | Preview/View |
| `rz-icon-ri--download-line` | Download/Export |
| `rz-icon-ri--upload-line` | Upload/Import |

::: tip
Browse all available icons at [remixicon.com](https://remixicon.com/). Use the icon name with the `rz-icon-ri--` prefix.
:::

## Complete Example

Here's a complete example of a custom listing page:

```twig
{# templates/admin/product/list.html.twig #}
{% extends "@RoadizRozier/admin/base.html.twig" %}

{%- block header -%}
    {% include '@RoadizRozier/admin/head.html.twig' with {
        title: 'products'|trans,
        filters: filters,
        buttons: [{
            label: 'add.product'|trans,
            attributes: { href: path('product_add') },
            icon: 'rz-icon-ri--add-line',
        }],
        breadcrumb: {
            label: 'product-breadcrumb',
            current: 'products'|trans,
        },
    } only %}
{%- endblock -%}

{%- block content_filters -%}
    {% embed "@RoadizRozier/widgets/rz_filters_bar.html.twig" with {
        filters: filters,
        display_select_all_button: hasBulkActions,
    } only %}
        {% block before_submit %}
            <select name="category" is="rz-select" aria-label="{{ 'filter.by_category'|trans }}">
                <option value="">{{ 'all.categories'|trans }}</option>
                {% for category in categories %}
                    <option {% if currentCategory == category.id %}selected{% endif %} value="{{ category.id }}">
                        {{ category.name }}
                    </option>
                {% endfor %}
            </select>
        {% endblock %}
    {% endembed %}
    
    {% include '@RoadizRozier/admin/bulk_actions.html.twig' %}
{%- endblock -%}

{%- block content_body -%}
    <article>
        {% embed '@RoadizRozier/includes/rz_table.html.twig' with {
            selection_available: hasBulkActions,
            actions_available: true,
            items: products,
        } only %}
            {% block thead_cells %}
                <th>{{ 'product.name'|trans }}</th>
                <th>{{ 'product.price'|trans }}</th>
                <th>{{ 'product.status'|trans }}</th>
            {% endblock %}
            
            {% block tbody_content %}
                {% for product in products %}
                    <tr>
                        {% if selection_available %}
                            <td>
                                {% import '@RoadizRozier/macros/rz_bulk.html.twig' as rz_bulk %}
                                {{ rz_bulk.select_input({ value: product.id }) }}
                            </td>
                        {% endif %}
                        <td>
                            <a href="{{ path('product_edit', { id: product.id }) }}">
                                {{ product.name }}
                            </a>
                        </td>
                        <td>{{ product.price|format_currency('EUR') }}</td>
                        <td>
                            {% import "@RoadizRozier/macros/rz_badge.html.twig" as rz_badge %}
                            {{ rz_badge.item({
                                label: product.status,
                                color: product.isPublished ? 'success' : 'warning',
                            }) }}
                        </td>
                        {% if actions_available %}
                            <td>
                                {% include '@RoadizRozier/includes/rz_table_actions.html.twig' with {
                                    edit_href: path('product_edit', { id: product.id }),
                                    delete_href: path('product_delete', { id: product.id }),
                                } %}
                            </td>
                        {% endif %}
                    </tr>
                {% else %}
                    <tr>
                        <td colspan="5">{{ 'no.products'|trans }}</td>
                    </tr>
                {% endfor %}
            {% endblock %}
        {% endembed %}
    </article>
{%- endblock -%}
```
