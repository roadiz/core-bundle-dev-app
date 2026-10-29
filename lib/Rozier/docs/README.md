# Rozier docs

This folder is a shared reference for developers and LLM agents working on the
Rozier backoffice. It explains the scope of the frontend codebase, how it fits
into the monorepo, and the team conventions for implementing changes.

Use it as a quick entry point when you touch anything in `lib/Rozier`.

## Start here

- `engineering-practices.md`: how we work, review, verify (feedback loop), and ship
- `ui-redesign-todo.md`: redesign zones, their order, the one-active-zone rule, and the component inventory
- `storybook.md`: how stories are organised, named and written
- `adr/`: accepted UI decisions (CustomElements over Vue/UIkit/jQuery, Figma as a starting point, accessibility never blocks, TypeScript renderers as the source of story markup)
- `../GLOSSARY.md`: UI and redesign vocabulary

## Scope and boundaries

Rozier is the Roadiz backoffice UI. Its source lives in `lib/Rozier` and is
built into `lib/RoadizRozierBundle/public/` for Symfony to serve.

- Rozier is the frontend application (UI, interactions, assets).
- Symfony bundles live in `lib/*Bundle` and the dev app in `src/`.
- Rozier changes often impact `lib/RoadizRozierBundle`, which consumes the compiled assets and owns the Twig templates.

## Codebase map

- `lib/Rozier/app/`: application source (TS/JS, styles, UI components, `custom-elements/`).
- `lib/Rozier/public/`: static public assets used by Vite.
- `lib/Rozier/stories/`: Storybook stories and UI experiments.
- `lib/Rozier/vite-plugins/`: Vite plugins and build helpers.

## Build output

- Local dev: `pnpm dev` in `lib/Rozier` (or `docker compose up node`).
- Production build: `pnpm build` (or `docker compose run --rm node pnpm build`).
- Output assets are emitted into `lib/RoadizRozierBundle/public/`: never edit them by hand.
- After a build, run `make cache`, then restart `app` and `nginx` (see the root README "Backoffice frontend development" section).

## What this folder is not

- End-user documentation or release notes
- Detailed UI specs or design assets (Figma is the source)
- Backend or bundle-level architecture reference
