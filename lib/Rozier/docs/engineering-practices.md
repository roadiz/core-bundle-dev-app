# Engineering practices

This document captures how we work on the Rozier UI as a team. Keep changes
small, reviewable, and aligned with the monorepo standards.

## Code style and architecture

- Prefer TypeScript for new or touched files.
- Keep modules focused and composable.
- Avoid global state and `window` side effects where possible.
- Favor native `CustomElements` to attach UI behavior.
- Keep UI logic close to the component that owns it.
- No new dependency without an explicit request.

See [ADR 0001](adr/0001-custom-elements-over-vue-uikit-jquery.md) for the
legacy stack being phased out.

## Rendering

- Render on the server what the page already knows. When content is known at
  page load or reachable through a URL without user interaction (prefilled
  values, list items, thumbnails, flash messages), Twig renders it and the
  custom element only adds behaviour. The screen does not have to work with
  JavaScript disabled.
- HTML rendered by Twig and fetched later (a tree loaded after the page, a
  refreshed fragment) follows this rule: the markup still has a single source.
  What the rule rules out is markup built in JavaScript from JSON.
- Content that only exists after a user action (search results, a Markdown
  preview, a dialog opened on click) may be rendered in JavaScript.
- The TypeScript renderers (`app/utils/component-renderer/`) exist for stories
  and for that interaction-driven content; they are not a way to render what
  Twig can render.
- Existing code is brought in line when its zone is worked on; there is no
  dedicated migration.

## Modernizing incrementally

- Keep changes incremental and easy to review.
- Avoid large rewrites without a clear migration path.
- Preserve existing behavior unless the design explicitly changes it: no
  silent behavior change slipped into a refactor.

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
2. Add or update the component's Storybook story (`docker compose up storybook`),
   following [`storybook.md`](storybook.md).
3. Compare with the matching Figma section when one exists.
4. Before opening the PR: `docker compose run --rm node pnpm lint` and
   `docker compose run --rm node pnpm build:check` pass, and so does
   `docker compose run --rm node pnpm build-storybook` when the change touches
   stories or renderers.

Edit sources only; `lib/RoadizRozierBundle/public/` holds build output.
`pnpm build:check` builds into `/tmp` so it leaves that folder untouched; run
`pnpm build` only when you mean to update the committed assets.

## Design decisions

Figma is the starting point, not a closed spec. When a mockup lacks a value,
a state or a whole screen, decide in line with the existing `rz-*` components
and design tokens, keep going, and record the choice (with a screenshot) in
the issue or PR. Escalate only product decisions. Rationale:
[ADR 0002](adr/0002-figma-is-a-starting-point.md).

## Accessibility

A standing attention point: contrast, visible focus, keyboard navigation,
accessible labels. Fix what is cheap in the same change; open a follow-up
issue for the rest. Findings never block a merge or the closing of a redesign
zone. Rationale: [ADR 0003](adr/0003-accessibility-never-blocks.md).

## Localization and UI text

- Avoid hardcoding user-facing strings; use the existing i18n path.
- Keep text changes consistent with the product tone.
- Never log sensitive data.

## Monorepo awareness

- Rozier assets are consumed by `lib/RoadizRozierBundle`.
- Keep changes compatible with bundle conventions.
