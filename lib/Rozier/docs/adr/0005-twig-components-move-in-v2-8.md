---
status: accepted
---

# Twig components move to a role-based tree in v2.8

> Partly superseded by [ADR 0002](../../../../docs/adr/0002-avoid-breaking-changes.md): the five released templates the 2.7 docs led projects to include keep a deprecated stub at their old path.

The Twig templates of `lib/RoadizRozierBundle` scatter each role (layouts, patterns, components) across `admin/`, `macros/`, `includes/` and `widgets/`, with duplicated components and option keys that differ from the TypeScript renderers ([ADR 0004](0004-shared-options-contract-for-twig-and-stories.md)). In v2.8 we move them to a role-based tree (`layouts/`, `patterns/`, `components/`) and rename component options after the renderers' `Options` types. The five released templates the 2.7 docs led projects to include keep a deprecated stub at their old path until 2.9; the other released templates are internal, and the components, patterns and options added by the unreleased redesign are not released yet, so both move and change freely ([ADR 0002](../../../../docs/adr/0002-avoid-breaking-changes.md)). We rejected applying the convention to new templates only (the scattering would stay for good).

## Consequences

- `layout.html.twig` and `forms.html.twig` stay at the root: they are the public entry points projects extend or reference, and are not moved. `dashboard/index.html.twig` is a public entry point too and stays in place ([ADR 0006](0006-projects-customise-through-public-mechanisms.md)).
- Once moved, components and patterns are public with their options ([ADR 0006](0006-projects-customise-through-public-mechanisms.md)); from their first release on, they follow the deprecation rule of [ADR 0002](../../../../docs/adr/0002-avoid-breaking-changes.md).
- Every moved or removed path released in 2.7 is listed in the "Upgrade to 2.8" section of `UPGRADE.md`: the five with a stub as deprecated, the others as removed.
- Twig component options and renderer `Options` share their keys, so drift between the two is checked key by key in review.
