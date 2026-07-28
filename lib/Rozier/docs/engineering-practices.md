# Engineering practices

This document captures how we work on the Rozier UI as a team. Keep changes
small, reviewable, and aligned with the monorepo standards.

## Code style and architecture

- Prefer TypeScript for new or touched files.
- Keep modules focused and composable.
- Avoid global state and `window` side effects where possible.
- Favor native `CustomElements` to attach UI behavior.
- Keep UI logic close to the component that owns it.

## Reviews and collaboration

- Keep PRs focused on a single topic.
- Explain intent, not only the mechanics of the change.
- Call out risky areas or follow-ups explicitly.

## Testing and build workflow

- Local dev: `pnpm dev` in `lib/Rozier` or `docker compose up node`.
- Build: `pnpm build` or `docker compose run --rm node pnpm build`.
- Avoid editing compiled assets in `lib/RoadizRozierBundle/public/`.

## Localization and UI text

- Avoid hardcoding user-facing strings; use the existing i18n path.
- Keep text changes consistent with the product tone.

## Monorepo awareness

- Rozier assets are consumed by `lib/RoadizRozierBundle`.
- Keep changes compatible with bundle conventions.
