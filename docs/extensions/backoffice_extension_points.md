# Backoffice extension points

Projects built on Roadiz customise the backoffice through **extension points**: the mechanisms listed on this page and on the pages it links to. They are public:

- an extension point never changes in a patch release;
- when it changes in a minor release, the change is listed in `UPGRADE.md`, with a deprecation period of one minor release when it costs little.

Anything not documented here is internal and may change in any release: template paths outside the entry points below, markup, CSS classes, the backoffice JavaScript and the raw color palette. Override an internal template only if you accept to check it on every upgrade.

Build your screens from the backoffice components rather than your own markup or custom elements. If a component you need is missing, ask for it in Roadiz.

## Overview

| Extension point | What you customise | Documentation |
|---|---|---|
| `roadiz_rozier.entries` | Main menu entries and sub-entries | [Create a new back office entry](./extending_roadiz.md#inject-your-own-entries-in-back-stage) |
| `roadiz_rozier.bookmarks` | Links in the header and on the dashboard | [Bookmarks](#bookmarks) |
| `roadiz_rozier.node_form`, `roadiz_rozier.add_node_form` | Node forms | [Node forms](#node-forms) |
| `UserActionsMenuEvent` | Actions of the user panel | [User panel actions](#user-panel-actions) |
| `roadiz_rozier.breadcrumbs_item_factory`, `roadiz_rozier.breadcrumb_root_provider` tags | Breadcrumbs of your entries | [Give your entry a breadcrumb](./custom_backoffice_entry.md#give-your-entry-a-breadcrumb) |
| `roadiz.explorer_provider` tag | Entities picked in explorers and drawers | [ConfigurableExplorerItem](./backoffice_templates.md#configurableexploreritem-for-custom-entities) |
| `roadiz.entity_thumbnail_provider` tag | Thumbnails of your entities | [Entity thumbnails](./entity-thumbnail.md) |
| `layout.html.twig`, `forms.html.twig` | Your own backoffice pages and forms | [Create a new back office entry](./extending_roadiz.md#create-a-new-back-office-entry), [Backoffice templates](./backoffice_templates.md) |
| `dashboard/index.html.twig` | Dashboard panels and header | [Dashboard](#dashboard) |
| `customStyles`, `customScripts` blocks | Extra CSS and JavaScript | [Custom styles and scripts](#custom-styles-and-scripts) |
| Design tokens | Colors of the backoffice | [Design tokens](#design-tokens) |
| Backoffice templates and macros | Building blocks for your pages | [Backoffice templates](./backoffice_templates.md) |
| `roadiz_rozier.translate_assistant` | Translation assistant | [Translate Assistant](./translate-assistant.md) |

## Bookmarks

Bookmarks are links shown in the backoffice header and in a dashboard panel. Each one needs a `label` and a `url`:

```yaml
# config/packages/roadiz_rozier.yaml
roadiz_rozier:
    bookmarks:
        - label: 'Documentation'
          url: 'https://docs.roadiz.io/'
        - label: '%env(string:APP_BOOKMARK_LABEL)%'
          url: '%env(string:APP_BOOKMARK_URL)%'
```

## Node forms

`node_form` replaces the form of a node's parameters page, and `add_node_form` the form used to add a node or a child node. Both take a Symfony `FormType` class:

```yaml
# config/packages/roadiz_rozier.yaml
roadiz_rozier:
    node_form: App\Form\NodeType
    add_node_form: App\Form\AddNodeType
```

The form receives the `Node` as data and a `nodeName` option. Start from the default types, `RZ\Roadiz\RozierBundle\Form\NodeType` and `RZ\Roadiz\RozierBundle\Form\Node\AddNodeType`, and keep their fields: the controller relies on them.

## User panel actions

Subscribe to `RZ\Roadiz\RozierBundle\Event\UserActionsMenuEvent` to add actions to the user panel of the header. Each action takes a label, a path and an icon class:

```php
<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use RZ\Roadiz\RozierBundle\Event\UserActionsMenuEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final readonly class UserActionsMenuSubscriber implements EventSubscriberInterface
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [UserActionsMenuEvent::class => 'onUserActionsMenu'];
    }

    public function onUserActionsMenu(UserActionsMenuEvent $event): void
    {
        $event->addAction(
            'My preferences',
            $this->urlGenerator->generate('appPreferencesPage'),
            'rz-icon-ri--settings-4-line'
        );
    }
}
```

## Dashboard

Customise the dashboard in two steps.

1. Override its template in `templates/bundles/RoadizRozierBundle/dashboard/index.html.twig`. Extend the original with `@!RoadizRozier`, then fill these blocks:
   - `dashboard_panels`, which renders your panels above the default ones;
   - `header`, which replaces the page header.
2. Decorate `RZ\Roadiz\RozierBundle\Controller\DashboardController` to pass your own data to the template. Your controller renders `@RoadizRozier/dashboard/index.html.twig`, which resolves to your override:

```yaml
# config/services.yaml
services:
    App\Controller\Admin\DashboardController:
        decorates: RZ\Roadiz\RozierBundle\Controller\DashboardController
```

```twig
{# templates/bundles/RoadizRozierBundle/dashboard/index.html.twig #}
{% extends "@!RoadizRozier/dashboard/index.html.twig" %}

{%- block dashboard_panels -%}
    <div class="rz-dashboard__row">
        {% embed '@RoadizRozier/patterns/dashboard_panel.html.twig' with {
            icon: 'rz-icon-ri--computer-line',
            title: 'My latest articles'|trans,
        } %}
            {% block content %}
                {# Your panel content #}
            {% endblock %}
        {% endembed %}
    </div>
{%- endblock -%}
```

The dev application of the Roadiz monorepo ships a full example in `src/Controller/Admin/DashboardController.php`.

## Custom styles and scripts

Pages extending `@RoadizRozier/layout.html.twig` expose two blocks:

- `customStyles`, in the document head, for `<link>` or `<style>` tags;
- `customScripts`, at the end of the body, for `<script>` tags.

```twig
{% extends '@RoadizRozier/layout.html.twig' %}

{% block customStyles %}
    <link rel="stylesheet" href="{{ asset('css/backoffice.css') }}">
{% endblock %}

{% block customScripts %}
    <script src="{{ asset('js/backoffice.js') }}" defer></script>
{% endblock %}
```

## Design tokens

The backoffice colors come from CSS custom properties defined in its theme. They switch between the light and dark themes with `light-dark()`. A project can override them from `customStyles`.

- The tokens defined in the theme are public, for example `--brand-primary`, `--brand-secondary`, `--badge-bg-on-light` and `--button-basic-high-bg-on-light`.
- The raw palette they refer to (`--color-*`) is internal.

Give an overridden token a value for both themes:

```css
:root {
    --brand-primary: light-dark(#0f6e5c, #5cc4ab);
}
```
