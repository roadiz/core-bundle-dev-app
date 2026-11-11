# Backoffice Templates System

Roadiz provides a modular template system for building custom back-office pages. Its building blocks live under `@RoadizRozier/ui/` and ensure visual consistency across the admin interface.

## Overview

Templates are sorted by their role in the interface:

| Layer | Path | Purpose |
|-------|------|---------|
| **Layouts** | `@RoadizRozier/ui/layouts/` | Page skeletons your pages extend (`admin`) |
| **Patterns** | `@RoadizRozier/ui/patterns/` | Screens and screen parts (page header, confirmation page, bulk actions bar) |
| **Components** | `@RoadizRozier/ui/components/` | One file per `rz-*` element (buttons, badges, tables, filters bar…) |
| **Utils** | `@RoadizRozier/ui/utils/` | Helpers with no rendering of their own |

## Generic Admin Templates

### `ui/layouts/admin.html.twig`

The base layout for listing pages. Extends `@RoadizRozier/layout.html.twig` and provides a structured content area with bulk selection support.

**Available blocks:**

| Block | Purpose |
|-------|---------|
| `header` | Page header with title, breadcrumb, and action buttons |
| `content_filters` | Search, filters, and bulk action buttons |
| `content_body` | Main content area (tables, cards, etc.) |

**Example usage:**

```twig
{% extends "@RoadizRozier/ui/layouts/admin.html.twig" %}

{%- block header -%}
    {% include '@RoadizRozier/ui/patterns/page_header.html.twig' with {
        title: 'my_entities'|trans,
        filters: filters,
        buttons: [{
            label: 'add.entity'|trans,
            attributes: { href: path('my_entity_add') },
            iconClass: 'rz-icon-ri--add-line',
        }]
    } only %}
{%- endblock -%}

{%- block content_filters -%}
    {% embed "@RoadizRozier/ui/components/rz_filters_bar.html.twig" with {
        filters: filters,
        displaySelectAllButton: true,
    } only %}{% endembed %}
{%- endblock -%}

{%- block content_body -%}
    <article>
        {# Your table or content here #}
    </article>
{%- endblock -%}
```

### `ui/patterns/page_header.html.twig`

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
    iconClass: 'rz-icon-ri--add-line',
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
{% include '@RoadizRozier/ui/patterns/page_header.html.twig' with {
    title: 'edit.article'|trans ~ ' "' ~ item.title ~ '"',
    filters: filters,
    buttons: [
        {
            label: 'add.article'|trans,
            attributes: { href: path('article_add') },
            iconClass: 'rz-icon-ri--add-line',
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

### `ui/patterns/confirm_action.html.twig`

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
return $this->render('@RoadizRozier/ui/patterns/confirm_action.html.twig', [
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
return $this->render('@RoadizRozier/ui/patterns/confirm_action.html.twig', [
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

    return $this->render('@RoadizRozier/ui/patterns/confirm_action.html.twig', [
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

## Components

Components live in `components/`, one file per `rz-*` element. Leaf components are macros you import; the ones with content slots are templates you embed.

### `ui/components/rz_button.html.twig`

Renders buttons and button-like links.

**Import and usage:**

```twig
{% import "@RoadizRozier/ui/components/rz_button.html.twig" as rz_button %}

{# Basic submit button #}
{{ rz_button.root({
    attributes: { type: 'submit' },
    label: 'save',
    emphasis: 'primary',
    iconClass: 'rz-icon-ri--save-line',
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
    iconClass: 'rz-icon-ri--delete-bin-7-line',
}) }}
```

**Options:**

| Option | Type | Description |
|--------|------|-------------|
| `label` | string | Button text (auto-translated) |
| `iconClass` | string | Icon class |
| `emphasis` | string | `primary`, `secondary`, `tertiary` |
| `color` | string | `danger`, `success`, `warning` |
| `size` | string | `sm`, `md`, `xs` |
| `attributes` | object | HTML attributes (`class`, `href`, `type`, `title`, etc.) |
| `tag` | string | HTML tag (default: `button`, auto `a` if `href`) |
| `is` | string \| false | Custom element (default: `rz-button`, `rz-link` with `href`); `false` for a `label` or `span` root |
| `innerHTML` | string | Markup rendered before the label and icon (e.g. a radio inside a `label` root) |
| `selected`, `disabled`, `onDark`, `hasPill` | bool | Modifiers |

The options are the keys of `RzButtonOptions` (`lib/Rozier/app/utils/component-renderer/rzButton.ts`); extra classes go in `attributes.class`.

### `ui/components/rz_badge.html.twig`

Renders badges for status indicators, counts, or labels.

```twig
{% import "@RoadizRozier/ui/components/rz_badge.html.twig" as rz_badge %}

{{ rz_badge.root({
    label: 'published',
    iconClass: 'rz-icon-ri--check-line',
    size: 'sm',
    color: 'success',
}) }}

{# Badge as link #}
{{ rz_badge.root({
    label: tag.name,
    attributes: { href: path('tag_edit', { id: tag.id }) },
}) }}
```

**Options:**

| Option | Type | Description |
|--------|------|-------------|
| `label` | string | Badge text |
| `iconClass` | string | Icon class |
| `size` | string | `xs`, `sm` |
| `color` | string | `success`, `danger`, `warning`, `default` |
| `innerHTML` | string | Markup rendered before the icon and label |
| `attributes` | object | HTML attributes (`class`, or `href` for a link badge) |

### `ui/components/rz_actions_menu.html.twig`

An embeddable template for action menus (typically positioned on the side of edit pages).

```twig
{% embed "@RoadizRozier/ui/components/rz_actions_menu.html.twig" with {
    vertical: true,
    class: 'actions-menu',
} only %}
    {% block items %}
        {{ _self.saveItem({ dataActionSave: '#my-form-id' }) }}
        {{ _self.deleteItem({ attributes: { href: path('entity_delete', { id: item.id }) } }) }}
        {{ _self.item({ button: {
            label: 'download',
            iconClass: 'rz-icon-ri--download-line',
            emphasis: 'tertiary',
            attributes: { href: path('entity_download', { id: item.id }) },
        } }) }}
    {% endblock %}
{% endembed %}
```

**Built-in macros:**

| Macro | Description |
|-------|-------------|
| `saveItem({ dataActionSave, label })` | Save button bound to a form (`RzSaveButtonOptions`) |
| `deleteItem({ label, attributes })` | Delete button/link, `attributes.href` for a link |
| `item({ button, popoverContent })` | Generic icon-only action item (`RzActionsMenuItemOptions`): `button` takes `rz_button` options, its `label` becomes the aria-label |

### `ui/components/rz_dropdown.html.twig`

Creates dropdown menus with customizable items.

```twig
{% embed "@RoadizRozier/ui/components/rz_dropdown.html.twig" with {
    targetId: 'my-dropdown',
    title: 'actions'|trans,
} %}
    {% block items %}
        {{ _self.item({
            label: 'edit',
            iconClass: 'rz-icon-ri--edit-line',
            attributes: { href: path('edit_route') },
        }) }}
        {{ _self.item({
            label: 'delete',
            iconClass: 'rz-icon-ri--delete-bin-7-line',
            attributes: { href: path('delete_route') },
        }) }}
    {% endblock %}
{% endembed %}
```

**Item options:**

| Option | Type | Description |
|--------|------|-------------|
| `label` | string | Item text |
| `description` | string | Secondary text |
| `iconClass` | string | Icon on the left |
| `rightIconClass` | string | Icon on the right |
| `badge` | object | `rz_badge` options |
| `selected` | bool | Highlight as selected |
| `tag` | string | HTML tag (default: `button`, auto `a` if `attributes.href`) |
| `attributes` | object | HTML attributes (`href`, `class`, `title`, etc.) |

The item options are the keys of `RzDropdownItemOptions` (`lib/Rozier/app/utils/storybook/renderer/rzDropDown.ts`). The embed takes `title`, `headElements`, `reverse`, `listTag` and `attributes` from `RzDropdownOptions`.

Pass `target_options` (the options of the `rz_button` that opens it) and the menu comes with its own trigger, wrapped in an `<rz-popover>`; `placement`, `offset`, `shift` and `popover_attributes` tune the popover. Without `target_options`, render the trigger yourself and point it at `targetId`.

### `ui/components/rz_link.html.twig`

Simple link component.

```twig
{% import '@RoadizRozier/ui/components/rz_link.html.twig' as rz_link %}

{{ rz_link.root({
    label: 'see.details',
    iconClass: 'rz-icon-ri--arrow-right-line',
    attributes: { href: path('detail_route') },
}) }}
```

Options: `label`, `iconClass`, `innerHTML` and `attributes` (with `class` and `href`).

### `ui/components/rz_message.html.twig`

Renders an inline message. Options are the keys of `RzMessageOptions` (`lib/Rozier/app/utils/component-renderer/rzMessage.ts`).

```twig
{% import "@RoadizRozier/ui/components/rz_message.html.twig" as rz_message %}

{{ rz_message.root({
    color: 'warning',
    iconClass: 'rz-icon-ri--error-warning-line',
    text: 'are_you_sure'|trans,
}) }}
```

| Option | Type | Description |
|--------|------|-------------|
| `text` | string | Message text (not translated, escaped unless it is markup) |
| `innerHTML` | string | Raw markup, used instead of `text` |
| `color` | string | `neutral`, `warning`, `error` |
| `variant` | string | `filled`, `ghost` |
| `iconClass` | string | Icon class, before the text |
| `textTag` | string | Tag of the text element (default `p`, `div` for block markup) |
| `attributes` | object | HTML attributes (`class`, etc.) |

### `ui/components/rz_form_field.html.twig`

Wraps a form control that is not rendered by the form theme. Embed it and put the control in the `body` block; the variables are the keys of `RzFormFieldOptions` (`lib/Rozier/app/utils/storybook/renderer/rzFormField.ts`).

```twig
{% embed "@RoadizRozier/ui/components/rz_form_field.html.twig" with {
    label: 'email',
    labelAttributes: { for: 'email' },
    required: true,
} only %}
    {% block body %}
        {% import "@RoadizRozier/ui/components/rz_input.html.twig" as rz_input %}
        {{ rz_input.root({ type: 'email', name: 'email', id: 'email', className: 'rz-form-field__body' }) }}
    {% endblock %}
{% endembed %}
```

| Variable | Type | Description |
|----------|------|-------------|
| `label` | string | Label (auto-translated) |
| `labelAttributes` | object | Label attributes (`for`, etc.) |
| `iconClass` | string | Icon before the label |
| `description` | string | Text under the label |
| `help` | string | Ghost message under the control |
| `error` | string | Error message under the control |
| `required`, `horizontal`, `alignStart` | bool | Modifiers |
| `attributes` | object | HTML attributes of the `rz-form-field` element |

Pass `only` and every variable the `body` block needs: an embed without `only` would read `label`, `error` or `help` from the page context. Macros imported outside the embed are not visible in the block: import them inside it.

### Form controls: `rz_input`, `rz_select`, `rz_switch`

Leaf macros for controls outside the form theme. Options are the keys of `RzInputOptions`, `RzSelectOptions` and `RzSwitchOptions` (`lib/Rozier/app/utils/storybook/renderer/`).

```twig
{% import "@RoadizRozier/ui/components/rz_input.html.twig" as rz_input %}
{% import "@RoadizRozier/ui/components/rz_select.html.twig" as rz_select %}
{% import "@RoadizRozier/ui/components/rz_switch.html.twig" as rz_switch %}

{{ rz_input.root({ type: 'search', name: 'search', placeholder: 'search'|trans }) }}

{{ rz_select.root({
    options: [
        { value: '', label: 'filter.by_tag.all'|trans },
        { label: 'Group', options: [{ value: '1', label: 'Tag 1', selected: true }] },
    ],
    attributes: { name: 'tag', 'data-submit-on-change': true },
}) }}

{{ rz_switch.root({ id: 'remember_me', name: 'remember_me', checked: true }) }}
```

`rz_input` and `rz_switch` take `type` (input only), `name`, `id`, `placeholder`, `value`, `required`, `className`, `checked` (switch only) and `attributes`. `rz_select` takes `options` (an option, or a group with `label` and `options`), `required` and `attributes`.

### `ui/components/rz_tablist.html.twig`

Renders a tab list. Options are the keys of `RzTablistOptions` (`lib/Rozier/app/utils/storybook/renderer/rzTablist.ts`).

```twig
{% import "@RoadizRozier/ui/components/rz_tablist.html.twig" as rz_tablist %}

{{ rz_tablist.root({
    tabs: [
        { innerHTML: 'general'|trans|e, selected: true, attributes: { id: 'tab-general' }, panel: { id: 'panel-general' } },
        { innerHTML: 'seo'|trans|e, attributes: { id: 'tab-seo' }, panel: { id: 'panel-seo' } },
    ],
}) }}
```

`innerHTML` is raw markup: escape text with `|e`.

### Bulk selection inputs (`ui/components/rz_bulk_actions.html.twig`)

Utilities for bulk selection in tables.

```twig
{% import '@RoadizRozier/ui/components/rz_bulk_actions.html.twig' as rz_bulk_actions %}

{# In table header #}
{{ rz_bulk_actions.select_all_input() }}

{# In table row #}
{{ rz_bulk_actions.select_input({ value: item.id }) }}
```

## Reusable Widgets

### `ui/components/rz_filters_bar.html.twig`

Complete filter bar with search, pagination, and bulk selection controls.

```twig
{% embed "@RoadizRozier/ui/components/rz_filters_bar.html.twig" with {
    filters: filters,
    displaySelectAllButton: true,
    displayLayoutFields: false,
} only %}{% endembed %}
```

**With custom filters using embed:**

```twig
{% embed "@RoadizRozier/ui/components/rz_filters_bar.html.twig" with {
    filters: filters,
    displaySelectAllButton: true,
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
| `displaySelectAllButton` | bool | Show "Select All" button |
| `displayLayoutFields` | bool | Show grid/list toggle |
| `withFilterForm` | bool | Enable search form (default: true) |

### `ui/components/rz_bulk_actions.html.twig`

Floating action bar that appears when items are selected.

```twig
{% embed "@RoadizRozier/ui/components/rz_bulk_actions.html.twig" %}
    {% import "@RoadizRozier/ui/components/rz_button.html.twig" as rz_button %}
    
    {% block other_actions %}
        {{ rz_button.root({
            attributes: { type: 'submit', form: 'bulk-publish-form' },
            emphasis: 'primary',
            color: 'success',
            iconClass: 'rz-icon-ri--check-line',
        }) }}
    {% endblock %}
    
    {% block delete_form %}
        <form action="{{ path('bulk_delete') }}" method="POST">
            {{ rz_button.root({
                attributes: { type: 'submit' },
                color: 'danger',
                emphasis: 'primary',
                iconClass: 'rz-icon-ri--delete-bin-7-line',
            }) }}
        </form>
    {% endblock %}
{% endembed %}
```

::: tip
For standard bulk actions (publish, unpublish, delete), use the pre-built `@RoadizRozier/ui/patterns/bulk_actions.html.twig` which handles form integration automatically:

```twig
{% include '@RoadizRozier/ui/patterns/bulk_actions.html.twig' %}
```

This requires `hasBulkActions`, `bulkPublishForm`, `bulkUnpublishForm`, and `bulkDeleteForm` variables from your controller.
:::

### `ui/components/rz_table.html.twig`

Embeddable table component with sorting, selection, and action columns.

```twig
{% embed '@RoadizRozier/ui/components/rz_table.html.twig' with {
    selectionAvailable: true,
    actionsAvailable: true,
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
                {% if selectionAvailable %}
                    <td>
                        {% import '@RoadizRozier/ui/components/rz_bulk_actions.html.twig' as rz_bulk_actions %}
                        {{ rz_bulk_actions.select_input({ value: item.id }) }}
                    </td>
                {% endif %}
                <td>{{ item.title }}</td>
                <td>{{ item.createdAt|format_datetime('short', 'short') }}</td>
                <td>
                    {% import "@RoadizRozier/ui/components/rz_badge.html.twig" as rz_badge %}
                    {{ rz_badge.root({ label: item.status }) }}
                </td>
                {% if actionsAvailable %}
                    <td>
                        {% embed '@RoadizRozier/ui/components/rz_table_actions.html.twig' with {
                            editHref: path('entity_edit', { id: item.id }),
                            deleteHref: path('entity_delete', { id: item.id }),
                        } %}{% endembed %}
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
| `selectionAvailable` | bool | Add checkbox column |
| `actionsAvailable` | bool | Add actions column header |
| `items` | array | Items to iterate |
| `sortable.enabled` | bool | Enable drag-and-drop sorting |
| `sortable.url` | string | URL for sort updates |
| `attributes` | object | HTML attributes of the `<table>` |

### `ui/components/rz_table_actions.html.twig`

Row action buttons (edit, delete, custom). Variables: `editHref`, `editTitle`, `editIconClass`, `deleteHref`, `deleteTitle`, `saveHref`, `saveTitle`, `saveClass`; `_self.button()` takes `rz_button` options.

```twig
{% embed '@RoadizRozier/ui/components/rz_table_actions.html.twig' with {
    editHref: path('entity_edit', { id: item.id }),
    deleteHref: path('entity_delete', { id: item.id }),
} %}
    {% block other_actions %}
        {{ _self.button({
            iconClass: 'rz-icon-ri--eye-line',
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
{% include '@RoadizRozier/ui/patterns/page_header.html.twig' with {
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
{% embed "@RoadizRozier/ui/components/rz_filters_bar.html.twig" with {
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
{% import '@RoadizRozier/ui/components/rz_button.html.twig' as rz_button %}

{%- block header -%}
    {% include '@RoadizRozier/ui/patterns/page_header.html.twig' with {
        title: "hello.%name%"|trans({'%name%': displayName}),
        breadcrumb: {
            label: 'breadcrumb',
            current: 'dashboard'|trans,
        },
        buttons: [
            {
                label: 'quick.action'|trans,
                attributes: { href: path('my_custom_route') },
                iconClass: 'rz-icon-ri--add-line',
            }
        ],
    } only %}
{%- endblock -%}

{%- block dashboard_panels -%}
    {{ parent() }}
    
    {# Add custom panel #}
    {% embed '@RoadizRozier/ui/patterns/dashboard_panel.html.twig' with {
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
{% extends '@RoadizRozier/ui/layouts/admin.html.twig' %}

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
{% extends "@RoadizRozier/ui/layouts/admin.html.twig" %}

{%- block header -%}
    {% include '@RoadizRozier/ui/patterns/page_header.html.twig' with {
        title: 'products'|trans,
        filters: filters,
        buttons: [{
            label: 'add.product'|trans,
            attributes: { href: path('product_add') },
            iconClass: 'rz-icon-ri--add-line',
        }],
        breadcrumb: {
            label: 'product-breadcrumb',
            current: 'products'|trans,
        },
    } only %}
{%- endblock -%}

{%- block content_filters -%}
    {% embed "@RoadizRozier/ui/components/rz_filters_bar.html.twig" with {
        filters: filters,
        displaySelectAllButton: hasBulkActions,
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
    
    {% include '@RoadizRozier/ui/patterns/bulk_actions.html.twig' %}
{%- endblock -%}

{%- block content_body -%}
    <article>
        {% embed '@RoadizRozier/ui/components/rz_table.html.twig' with {
            selectionAvailable: hasBulkActions,
            actionsAvailable: true,
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
                        {% if selectionAvailable %}
                            <td>
                                {% import '@RoadizRozier/ui/components/rz_bulk_actions.html.twig' as rz_bulk_actions %}
                                {{ rz_bulk_actions.select_input({ value: product.id }) }}
                            </td>
                        {% endif %}
                        <td>
                            <a href="{{ path('product_edit', { id: product.id }) }}">
                                {{ product.name }}
                            </a>
                        </td>
                        <td>{{ product.price|format_currency('EUR') }}</td>
                        <td>
                            {% import "@RoadizRozier/ui/components/rz_badge.html.twig" as rz_badge %}
                            {{ rz_badge.root({
                                label: product.status,
                                color: product.isPublished ? 'success' : 'warning',
                            }) }}
                        </td>
                        {% if actionsAvailable %}
                            <td>
                                {% embed '@RoadizRozier/ui/components/rz_table_actions.html.twig' with {
                                    editHref: path('product_edit', { id: product.id }),
                                    deleteHref: path('product_delete', { id: product.id }),
                                } %}{% endembed %}
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
