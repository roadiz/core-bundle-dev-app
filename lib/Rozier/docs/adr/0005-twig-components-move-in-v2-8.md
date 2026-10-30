---
status: accepted
---

# Twig components move to a role-based tree in v2.8

The Twig templates of `lib/RoadizRozierBundle` scatter each role (layouts, patterns, components) across `admin/`, `macros/`, `includes/` and `widgets/`, with duplicated components and option keys that differ from the TypeScript renderers ([ADR 0004](0004-shared-options-contract-for-twig-and-stories.md)). In v2.8 we move them to a role-based tree (`layouts/`, `patterns/`, `components/`) and rename component options after the renderers' `Options` types, even though projects that override or include `@RoadizRozier/…` templates break. v2.8 already ships the UI redesign, which breaks most overrides anyway. We rejected applying the convention to new templates only (the scattering would stay for good) and keeping deprecated aliases until v3 (dozens of stub files to maintain for paths few projects use).

## Consequences

- `layout.html.twig` and `forms.html.twig` stay at the root: they are the public entry points projects extend or reference, and are not moved. `dashboard/index.html.twig` is a public entry point too and stays in place ([ADR 0006](0006-projects-customise-through-public-mechanisms.md)).
- Once moved, components and patterns are public with their options ([ADR 0006](0006-projects-customise-through-public-mechanisms.md)): this move is the last break of their paths without the deprecation rules of that ADR.
- Every other moved or removed path, and every renamed component option, is listed in the "Upgrade to 2.8" section of `UPGRADE.md`.
- Twig component options and renderer `Options` share their keys, so drift between the two is checked key by key in review.
