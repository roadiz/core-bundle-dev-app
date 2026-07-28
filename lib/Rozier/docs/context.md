# Rozier context

Rozier is the Roadiz backoffice UI. In this monorepo, its source code lives in
`lib/Rozier` and is built into `lib/RoadizRozierBundle/public/` for Symfony to
serve.

## Scope and boundaries

- Rozier is the frontend application (UI, interactions, assets).
- Symfony bundles live in `lib/*Bundle` and the dev app in `src/`.
- Do not edit generated assets in `lib/RoadizRozierBundle/public/`.

## Codebase map (high level)

- `lib/Rozier/app/`: application source (JS/TS, styles, UI components).
- `lib/Rozier/public/`: static public assets used by Vite.
- `lib/Rozier/stories/`: Storybook stories and UI experiments.
- `lib/Rozier/vite-plugins/`: Vite plugins and build helpers.

## Build output

- Local dev: `pnpm dev` in `lib/Rozier` (or `docker compose up node`).
- Production build: `pnpm build` (or `docker compose run --rm node pnpm build`).
- Output assets are emitted into `lib/RoadizRozierBundle/public/`.

## Why this matters

- Rozier changes often impact the PHP bundle that consumes compiled assets.
- Keep frontend changes aligned with bundle conventions and CI checks.
