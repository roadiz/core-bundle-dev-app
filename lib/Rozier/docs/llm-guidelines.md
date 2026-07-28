# LLM guidelines

This document helps automated agents work safely and effectively in the Rozier
codebase. Follow these defaults unless a maintainer asks otherwise.

## Safe defaults

- Read existing code first; mirror local patterns.
- Prefer TypeScript for new or modified files.
- Avoid globals and side effects on `window`.
- Use native `CustomElements` to encapsulate UI behavior.
- Keep changes small, focused, and easy to review.

## Codebase boundaries

- Source lives in `lib/Rozier/app/` and related folders.
- Build output goes to `lib/RoadizRozierBundle/public/`.
- Do not edit generated assets in `lib/RoadizRozierBundle/public/`.

## Workflow hints

- Local dev: `pnpm dev` in `lib/Rozier` (or `docker compose up node`).
- Build: `pnpm build` (or `docker compose run --rm node pnpm build`).
- If you change UI strings, follow the existing localization approach.

## What to avoid

- New dependencies without an explicit request.
- Silent behavior changes or large refactors.
- Hardcoding user-facing strings.
- Logging sensitive data.
