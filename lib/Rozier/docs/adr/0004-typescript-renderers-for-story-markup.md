---
status: accepted
---

# TypeScript renderers are the source of story markup

In production, Twig templates in `lib/RoadizRozierBundle` render the backoffice markup; Storybook cannot run them, since it has no PHP runtime. Stories therefore build their markup through TypeScript **renderers** (`app/utils/component-renderer/` and `app/utils/storybook/renderer/`), and those renderers are the only source of the markup shown in Storybook. We rejected generating HTML fixtures from Twig (zero drift, but a Symfony-to-Storybook pipeline to build and keep running) and making renderers the single source reproduced by Twig (a rewrite of the template layer).

## Consequences

- Renderer and Twig markup can drift apart. A PR that changes the markup of an `rz-*` root in Twig updates its renderer in the same PR, and the other way round; `/code-review` checks it against [`storybook.md`](../storybook.md).
- A story proves the component's styles and behaviour on the markup its renderer produces, not on the exact markup Twig ships.
- If drift becomes a recurring source of bugs, this ADR is superseded by a Twig fixture pipeline rather than worked around.
