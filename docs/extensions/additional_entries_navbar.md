# Navigation Bars

The navigation bar system in `RoadizRozierBundle` is standardized across all entity detail pages. Whether you are on a Node, User, Tag, or Document page, the template logic remains extensible.

This allows child templates to add custom buttons or disable certain default elements without rewriting the entire menu construction logic.

## Adding Custom Entries

To add a button at the end of the navigation bar, use the `additional_entries` block.

You must import the `rz_menu_bar.html.twig` macro to use the `rz_menu_bar.item()` method for creating your button.

### Generic Example

Here is how to add a button. You simply need to change the template you extend (see the list below).

```twig
{# templates/bundles/RoadizRozierBundle/users/navBar.html.twig #}

{# 1. Extend the specific navBar you want to customize (see list below) #}
{% extends "@!RoadizRozier/users/navBar.html.twig" %}

{# 2. Import the macro #}
{% import "@!RoadizRozier/macros/rz_menu_bar.html.twig" as rz_menu_bar %}

{# 3. Add your content #}
{% block additional_entries %}
    {{ rz_menu_bar.item(
        'my_custom_action',
        'My Action',
        '#',
        current,
        'rz-icon-ri--magic-line',
        iconOnly: true
    ) }}
{% endblock %}
```

::: tip Positioning
Items added via `additional_entries` will always appear **after** the standard groups, aligned at the end of the bar.
the new items group will be separated by a vertical divider.
:::

### Available Navigation Bars

Depending on the page you want to customize, extend one of the following templates:

| Context | Template Path |
| :--- | :--- |
| **Nodes** | `@RoadizRozier/nodes/navBar.html.twig` |
| **Users** | `@RoadizRozier/users/navBar.html.twig` |
| **Tags** | `@RoadizRozier/tags/navBar.html.twig` |
| **Documents** | `@RoadizRozier/documents/navBar.html.twig` |
| **Folders** | `@RoadizRozier/folders/navBar.html.twig` |
| **User Groups** | `@RoadizRozier/groups/navBar.html.twig` |
| **Custom Forms** | `@RoadizRozier/custom-forms/navBar.html.twig` |

## Disabling or Overriding Default Items

Certain native elements are conditioned by specific blocks (like the SEO button on Nodes). You can disable or override them by redefining the corresponding block in your template.

### Example: Disabling Node SEO

```twig
{% extends "@RoadizRozier/nodes/navBar.html.twig" %}

{# Disables the SEO button display in the "Content" group #}
{% block node_seo %}{% endblock %}

{# You can still add your own buttons afterward #}
{% block additional_entries %}
   ...
{% endblock %}
```

### Example: Overriding an Item

You can replace a default button with your own implementation:

```twig
{% extends "@RoadizRozier/nodes/navBar.html.twig" %}
{% import "@!RoadizRozier/macros/rz_menu_bar.html.twig" as rz_menu_bar %}

{# Overrides the SEO button display #}
{% block node_seo %}
    {{ rz_menu_bar.item(
        'custom_seo',
        'Custom SEO',
        path('custom_seo_route', { nodeId: node.id }),
        current,
        'rz-icon-ri--seo-line',
    ) }}
{% endblock %}
```

## Macro Documentation `rz_menu_bar.item`

The `rz_menu_bar.item()` method generates an `<li>` element containing a link formatted according to the Rozier design system.

| Argument | Type | Required | Description |
| :--- | :--- | :--- | :--- |
| `name` | `string` | **Yes** | Unique identifier for the item (used for the active CSS class). |
| `label` | `string` | **Yes** | Translation key or label displayed on hover or as text. |
| `href` | `string` | **Yes** | The destination URL (use `path()`). |
| `current` | `string` | **Yes** | The identifier of the current page (to mark the tab as active). |
| `icon` | `string` | No | CSS class for the icon (e.g., `rz-icon-ri--home-line`). |
| `target` | `string` | No | Link target attribute (e.g., `_blank`). |
| `iconOnly` | `bool` | No | If `true`, hides the label and shows only the icon (default: `false`). |

### Full Signature

```twig
{{ rz_menu_bar.item(name, label, href, current, icon, target, iconOnly) }}
```
