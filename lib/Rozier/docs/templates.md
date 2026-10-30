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
  layouts/            page skeletons: base, simple, admin, login, head, meta_icon
  patterns/           layout regions and screen parts: header, aside, menu_bar,
                      dashboard_nav, dashboard_panel
  components/         one file per rz-* root: rz_button.html.twig, rz_table.html.twig…
  form/               form theme variants: horizontal_forms
  macros/             helpers without an rz-* root: element_attributes
  includes/           shared partials without an rz-* root: column_ordering,
                      messages, translate_assistant_usage
  <feature>/          pages and their local partials (nodes/, documents/, users/…)
  widgets/            legacy trees only, until the Navigation zone replaces them
```

- Projects can rely on `layout.html.twig`, `forms.html.twig` and
  `dashboard/index.html.twig`, and on the components and patterns of
  `components/` and `patterns/` with their options, once documented in
  `docs/extensions/`. Everything else is internal to the bundle
  ([ADR 0006](adr/0006-projects-customise-through-public-mechanisms.md)).
- Page templates keep their current names and folders: controllers resolve many
  of them through `getTemplateFolder()`.
- New files in `layouts/`, `patterns/`, `components/`, `form/`, `macros/` and
  `includes/` are named in snake_case. A component file is named after its
  `rz-*` root: `rz-header-nav-item` → `components/rz_header_nav_item.html.twig`.
- A partial used by a single feature stays in that feature's folder; it moves to
  `includes/` or `components/` once a second feature needs it.

## Components

One file per `rz-*` root, never duplicated across folders.

- **Leaf component**: a macro named `root`, taking a single `options` hash.
  Named sub-parts (`item`, `inner`) are extra macros in the same file.
- **Component with content slots** (table, actions menu): the file is embedded
  and exposes blocks; it may also define macros for its items.
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
  (the header nav item is `components/rz_header_nav_item.html.twig`).
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
