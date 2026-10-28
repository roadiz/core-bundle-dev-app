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

## Feedback loop

Rozier has no automated tests, so every change is verified through this loop
(it replaces test-first work on the PHP side):

1. Run `docker compose up node` and check the change live on the backoffice
   (HMR, see the README "Backoffice frontend development" section), in both
   light and dark themes.
2. Add or update the component's Storybook story (`docker compose up storybook`).
3. Compare with the matching Figma section when one exists.
4. Before opening the PR: `docker compose run --rm node pnpm lint` and
   `docker compose run --rm node pnpm build` pass.

Edit sources only; `lib/RoadizRozierBundle/public/` holds build output.

## Design decisions

Figma is the starting point, not a closed spec. When a mockup lacks a value,
a state or a whole screen, decide in line with the existing `rz-*` components
and design tokens, keep going, and record the choice (with a screenshot) in
the issue or PR so the team can revisit it. Escalate to the team only for
product decisions, never for a missing mockup detail.

## Accessibility

Treat accessibility as a standing attention point: contrast, visible focus,
keyboard navigation, accessible labels. Fix what is cheap in the same change;
open a follow-up issue for the rest. Accessibility findings never block a
merge or the closing of a redesign zone.

## Localization and UI text

- Avoid hardcoding user-facing strings; use the existing i18n path.
- Keep text changes consistent with the product tone.

## Monorepo awareness

- Rozier assets are consumed by `lib/RoadizRozierBundle`.
- Keep changes compatible with bundle conventions.
