---
status: accepted
---

# Projects customise the backoffice through public mechanisms

> Partly superseded by [ADR 0002](../../../../docs/adr/0002-avoid-breaking-changes.md): a public extension point changing in a minor release is deprecated for one minor release first, except for a security fix or a dependency's major upgrade that forces the break; and a mechanism is public once any page of `docs/` written for projects documents it, not only `docs/extensions/`.

Projects built on Roadiz need to customise strategic parts of the backoffice (menu, dashboard, listings of their own node types) without breaking on every release. Rather than listing the screens a project may touch, we promise stability on **extension points**: the mechanisms a project customises through. An extension point is public when it is documented in `docs/extensions/`; anything undocumented is internal and may change in any release. Projects compose their screens from Rozier's components, as with a design system, instead of shipping their own `rz-*` elements.

The public mechanisms are:

- `roadiz_rozier.*` bundle configuration
- dependency injection tags and events
- Twig blocks of the public entry points (`layout.html.twig`, `forms.html.twig`, `dashboard/index.html.twig`)
- Twig components and patterns with their options, once [ADR 0005](0005-twig-components-move-in-v2-8.md) has moved them
- the design tokens of `theme.css`

Template paths outside those entry points, markup, CSS classes, TypeScript code and the raw palette of `variables/colors.css` stay internal.

We rejected making every `@RoadizRozier` template overridable (any markup change would break a project, so the backoffice could no longer evolve) and declaring public screens one by one (each new strategic screen would need its own decision).

## Consequences

- A public extension point never changes in a patch release. It may change in a minor release, listed in `UPGRADE.md`, after a one-minor deprecation ([ADR 0002](../../../../docs/adr/0002-avoid-breaking-changes.md)).
- Documenting a mechanism in `docs/extensions/` makes it public; a PR that documents one takes on that promise, and a PR that changes one updates its page and `UPGRADE.md`.
- The dashboard is customised by overriding its template and decorating its controller; `dashboard/index.html.twig` therefore stays in place like `layout.html.twig` and `forms.html.twig`.
- A strategic screen that projects cannot customise through a public mechanism is a gap to open as an issue, not an invitation to override an internal template.
- A project needing a component Rozier lacks asks for it in Rozier rather than registering its own `rz-*` element.
