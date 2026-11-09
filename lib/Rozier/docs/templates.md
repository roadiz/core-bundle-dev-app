# Twig templates

How the templates of `lib/RoadizRozierBundle/templates/` are organised and how
components are written. Vocabulary (layout, pattern, component) lives in
[`../GLOSSARY.md`](../GLOSSARY.md). Rationale for the tree and the v2.8 break:
[ADR 0005](adr/0005-twig-components-move-in-v2-8.md).

This page holds two kinds of guidance:

- **Rules** protect a contract: the options shared with the TypeScript
  renderers, the paths projects rely on, one source per component. Follow them;
  an exception is rare and justified in the PR.
- **Guidelines** help decide where a template goes and how far to factor it.
  They are a starting point, not a gate: the developer decides, in the same
  spirit as [ADR 0002](adr/0002-figma-is-a-starting-point.md). When a case does
  not fit, pick what reads best and say why in the PR.

## Tree

```
templates/
  layout.html.twig    public entry point: pages extend it
  forms.html.twig     public form theme
  ui/                 design system building blocks, public once documented
    layouts/          document and page skeletons: base, simple, admin, login, page_preview, meta_icon
    patterns/         layout regions, screens and screen parts: header, aside,
                      menu_bar, page_header, confirm_action, bulk_actions,
                      messages, meta_item_table, dashboard_nav, dashboard_panel…
    components/       one file per rz-* root: rz_button.html.twig, rz_table.html.twig…
    utils/            helpers with no rendering of their own: element_attributes
  form/               form theme variants and partials: horizontal_forms,
                      document_alignment
  <feature>/          pages and their local partials (nodes/, documents/, webhooks/…)
  widgets/            legacy only: trees until the Navigation zone replaces them,
                      countFiltersBar kept and filtersBar deprecated stub for 2.7
                      projects
  admin/, includes/   deprecated stubs only, removed in 2.9
```

`ui/` separates the building blocks from the screens because that is the line
of the stability promise: documented components and patterns are extension
points, screens are internal. It stays inside the `@RoadizRozier` namespace, so
projects override any file the usual way, under
`templates/bundles/RoadizRozierBundle/`.

## Rules

### Components

- One file per `rz-*` root, in `ui/components/rz_<element>.html.twig`, never
  duplicated across folders. Its sub-parts are macros of the same file
  (`rz_table.column_ordering`, `rz_breadcrumb.item`).
- Markup with an `rz-*` root goes through its component, in pages, patterns
  and layouts alike; it is not written by hand.
- A layout of `ui/layouts/` owns the `rz-*` root of its page skeleton
  (`rz-page` in `base`, `rz-login` in `login`, `rz-page-preview` in
  `page_preview`), styled by its own native CSS file: pages extend the layout
  instead of rewriting that root.
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

### Paths and stability

- Public entry points stay where they are: `layout.html.twig`,
  `forms.html.twig`, `dashboard/index.html.twig`
  ([ADR 0006](adr/0006-projects-customise-through-public-mechanisms.md)).
- Projects can rely on those entry points and on the files of `ui/` with their
  options, once a page of `docs/` written for projects documents them.
  Everything else is internal to the bundle
  ([ADR 0002](../../../docs/adr/0002-avoid-breaking-changes.md)).
- A moved template the 2.7 docs led projects to include keeps a stub at its old
  path until 2.9, starting with `{% deprecated %}`:
  `admin/base` extends `ui/layouts/admin`, `admin/bulk_actions` includes
  `ui/patterns/bulk_actions`, `includes/column_ordering` calls
  `rz_table.column_ordering`.
- Page templates keep their current names and folders: controllers resolve many
  of them through `getTemplateFolder()`.
- `widgets/` takes nothing new.
- Files of `ui/` and `form/` are named in snake_case. The `rz_` prefix is
  reserved for `ui/components/`, whose file is named after its `rz-*` element:
  `rz-header-nav-item` → `ui/components/rz_header_nav_item.html.twig`.
  Elsewhere the folder already says the role: `ui/patterns/header`,
  `ui/layouts/admin`.

### Verification

Before opening a PR that moves, renames or removes a template:

1. No reference to an old path is left: `grep -rn "<old path>" lib src config`.
2. `make test` passes (it runs `lint:twig` on the bundle; `lint:twig` does not
   catch an `include` towards a missing path, hence step 1).
3. Click through every touched screen in both themes; list them as a checklist
   in the PR.
4. The "Upgrade to 2.8" section of `UPGRADE.md` lists what a project on the
   previous release must change: a moved or removed path, a renamed option of
   an element that existed in that release. A change to an element new in 2.8
   is not upgrade information: leave it out.

## Guidelines

### Pages

A page in `<feature>/` assembles layouts, patterns and components of `ui/`.
Plain layout markup around them (wrappers, grids, a heading) is written in the
page; only `rz-*` roots must go through a component (see Rules).

When a partial is shared:

- by several pages of the same feature, it stays in `<feature>/`;
- by a second feature, it is a good time to move it to `ui/`;
- sooner, if it is plainly generic from its first use;
- not at all, if two features only look alike by coincidence.

Moving to `ui/` puts the file on the stability line, so promote what you are
ready to keep stable. Two screens that share a look but no structure share CSS
in the component, not a new template.

### Where a template goes

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

These questions usually settle it, in order (1 and 5 restate rules):

1. **Is it a public entry point** that pages extend or forms are themed with?
   It stays where it is.
2. **Is it a page, or a partial used by a single feature?** `<feature>/`
   (see Pages).
3. **Does it hold the document or page skeleton, with no content of its own?**
   `ui/layouts/`. Pages extend a layout; they do not include one.
4. **Is it a layout region, a whole screen or a screen part assembling
   components** (header, menu bar, page header, confirmation screen, bulk
   actions bar, flash messages)? `ui/patterns/`. A building block reused as is
   across screens is a component instead.
5. **Does it render an `rz-*` element?** `ui/components/`.
6. **Is it a form theme variant or a form widget partial?** `form/`.
7. **Is it a helper with no markup of its own, no `rz-*` element and no page
   context?** `ui/utils/`. Once a helper renders interface, it is a component or
   a pattern; once its logic grows, it becomes a Twig extension in PHP.

Edge cases:

- A component whose outer element is another component is named after the
  element it exists to render: `rz_drawer` and `rz_geotag` open on
  `<rz-form-field>`, `rz_color_scheme_switcher` embeds `rz_dropdown`.
- A pattern may wrap a component of a close name: `ui/patterns/page_header`
  fills `ui/components/rz_page_header`. The component is the markup, the
  pattern decides what goes in it.

### Patterns and layouts

- A pattern is a layout region or a screen part (header, aside, menu bar,
  dashboard panels). Its items that are reused elsewhere are components
  (the header nav item is `ui/components/rz_header_nav_item.html.twig`).
- Layouts mostly hold the page skeleton; a region with its own `rz-*` root
  inside them is usually better as a pattern or a component.
