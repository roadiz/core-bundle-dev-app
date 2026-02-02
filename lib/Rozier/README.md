# Rozier

Rozier is the Roadiz backoffice UI. Its frontend source code lives in this
folder and is built into `lib/RoadizRozierBundle/public/` for Symfony to serve.

## Start here

- `docs/README.md`: developer and LLM documentation

## Quick notes

- Do not edit generated assets in `lib/RoadizRozierBundle/public/`.
- Use `pnpm dev` in `lib/Rozier` (or `docker compose up node`) for local dev.
- Use `pnpm build` (or `docker compose run --rm node pnpm build`) for builds.
