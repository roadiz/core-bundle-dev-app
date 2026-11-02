# Twig templates

How the templates of `lib/RoadizRozierBundle/templates/` are organised and how
components are written. Vocabulary (layout, pattern, component) lives in
[`../GLOSSARY.md`](../GLOSSARY.md). Rationale for the tree and the v2.8 break:
[ADR 0005](adr/0005-twig-components-move-in-v2-8.md).

## Tree

```
templates/
  layout.html.twig    public entry point: pages extend it
  forms.html.twig     public form theme
  ui/                 design system building blocks, public once documented
    layouts/          document and page skeletons: base, simple, admin, login, meta_icon
    patterns/         layout regions, screens and screen parts: header, aside,
                      menu_bar, page_header, confirm_action, bulk_actions,
                      messages, meta_item_table, dashboard_nav, dashboard_panel…
    components/       one file per rz-* root: rz_button.html.twig, rz_table.html.twig…
    utils/            helpers with no rendering of their own: element_attributes
  form/               form theme variants and partials: horizontal_forms,
                      document_alignment
  <feature>/          pages and their local partials (nodes/, documents/, webhooks/…)
  widgets/            legacy only: trees until the Navigation zone replaces them,
                      countFiltersBar kept for 2.7 projects
  admin/, includes/   deprecated stubs only, removed in 2.9
```

## Where a template goes

The folder says what a template **is** in the interface, never how it is
called. Until 2.7 the tree followed the Twig mechanism instead: `macros/` held
what is imported, `includes/` what is included, `widgets/` what is embedded.
One component ended up in several folders (`rz_repeatable` in `macros/` and
`widgets/`, `rz_dropdown` split between `includes/` and `macros/`), and
`admin/` mixed a layout, a pattern, a generic screen and a feature.

The mechanism (macro, `include`, `embed`, `extends`) is part of the file's
contract, written in its header comment, not of its path:

- the file already declares its macros and blocks, and every call shows the
  mechanism;
- one building block often needs several mechanisms (`rz_dropdown` is embedded
  and provides a macro, `rz_breadcrumb` has `root` and `item` macros);
- the mechanism changes with refactors while the role stays, and a documented
  path is an extension point: moving a file because its mechanism changed
  would be a breaking change ([ADR 0002](../../../docs/adr/0002-avoid-breaking-changes.md)).

Answer these in order; the first yes decides:

1. **Is it a public entry point** that pages extend or forms are themed with?
   It stays where it is: `layout.html.twig`, `forms.html.twig`,
   `dashboard/index.html.twig` ([ADR 0006](adr/0006-projects-customise-through-public-mechanisms.md)).
2. **Is it a page, or a partial used by a single feature?** `<feature>/`.
   Controllers resolve pages through `getTemplateFolder()`. A partial moves to
   `ui/` once a second feature needs it.
3. **Does it hold the document or page skeleton, with no content of its own?**
   `ui/layouts/`. Pages extend a layout; they never include one.
4. **Is it a layout region, a whole screen or a screen part assembling
   components** (header, menu bar, page header, confirmation screen, bulk
   actions bar, flash messages)? `ui/patterns/`. A building block reused as is
   across screens is a component instead.
5. **Does it render an `rz-*` element?** `ui/components/rz_<element>.html.twig`,
   one file per element; its sub-parts are macros of the same file
   (`rz_table.column_ordering`, `rz_breadcrumb.item`).
6. **Is it a form theme variant or a form widget partial?** `form/`.
7. **Is it a helper with no markup of its own, no `rz-*` element and no page
   context?** `ui/utils/`. Once a helper renders interface, it is a component or
   a pattern; once its logic grows, it becomes a Twig extension in PHP.

`widgets/` takes nothing new.

`ui/` separates the building blocks from the screens because that is the line
of the stability promise: documented components and patterns are extension
points, screens are internal. It stays inside the `@RoadizRozier` namespace, so
projects override any file the usual way, under
`templates/bundles/RoadizRozierBundle/`.

Edge cases:

- A component whose outer element is another component is named after the
  element it exists to render: `rz_drawer` and `rz_geotag` open on
  `<rz-form-field>`, `rz_color_scheme_switcher` embeds `rz_dropdown`.
- A pattern may wrap a component of a close name: `ui/patterns/page_header`
  fills `ui/components/rz_page_header`. The component is the markup, the
  pattern decides what goes in it.

## Naming and stability

- Files of `ui/` and `form/` are named in snake_case. The `rz_` prefix is
  reserved for `ui/components/`, whose file is named after its `rz-*` element:
  `rz-header-nav-item` → `ui/components/rz_header_nav_item.html.twig`.
  Elsewhere the folder already says the role: `ui/patterns/header`,
  `ui/layouts/admin`.
- Page templates keep their current names and folders: controllers resolve many
  of them through `getTemplateFolder()`.
- Projects can rely on `layout.html.twig`, `forms.html.twig` and
  `dashboard/index.html.twig`, and on the files of `ui/` with their options,
  once a page of `docs/` written for projects documents them. Everything else is
  internal to the bundle
  ([ADR 0006](adr/0006-projects-customise-through-public-mechanisms.md),
  [ADR 0002](../../../docs/adr/0002-avoid-breaking-changes.md)).
- A moved template the 2.7 docs led projects to include keeps a stub at its old
  path until 2.9, starting with `{% deprecated %}`:
  `admin/base` extends `ui/layouts/admin`, `admin/bulk_actions` includes
  `ui/patterns/bulk_actions`, `includes/column_ordering` calls
  `rz_table.column_ordering`.

## Components

One file per `rz-*` root, never duplicated across folders.

- **Leaf component**: a macro named `root`, taking a single `options` hash.
  Named sub-parts (`item`, `inner`) are extra macros in the same file.
- **Component with content slots** (table, actions menu): the file is embedded
  and exposes blocks; it may also define macros for its items. Macros imported
  by the embedding file are not visible inside the embedded blocks: import the
  component again inside the block that calls its macros.
- A component is never pulled in with `include`.
- `options` keys are the keys of the renderer's `Options` type
  (`app/utils/component-renderer/` or `app/utils/storybook/renderer/`):
  `iconClass`, not `icon`; `tag`, not `tagName`. A PR that changes one changes
  the other ([ADR 0004](adr/0004-shared-options-contract-for-twig-and-stories.md)).
- The file opens with a one-line comment naming the `Options` type and the story:

```twig
{# rz-button: options = RzButtonOptions (component-renderer/rzButton.ts), story Components/Actions/Button #}
{% macro root(options = {}) %}
    …
{% endmacro %}
```

- Markup with an `rz-*` root goes through its component; it is not written by
  hand in a page.

## Patterns and layouts

- A pattern is a layout region or a screen part (header, aside, menu bar,
  dashboard panels). Its items that are reused elsewhere are components
  (the header nav item is `ui/components/rz_header_nav_item.html.twig`).
- Layouts only hold the page skeleton; anything with its own `rz-*` root inside
  them is a pattern or a component.

## Verification

Before opening a PR that moves, renames or removes a template:

1. No reference to an old path is left: `grep -rn "<old path>" lib src config`.
2. `make test` passes (it runs `lint:twig` on the bundle; `lint:twig` does not
   catch an `include` towards a missing path, hence step 1).
3. Click through every touched screen in both themes; list them as a checklist
   in the PR.
4. The "Upgrade to 2.8" section of `UPGRADE.md` lists the moved or removed path
   or the renamed option.
