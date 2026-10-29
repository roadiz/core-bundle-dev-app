# Domain Docs

How the engineering skills should consume this repo's domain documentation when exploring the codebase.

## Before exploring, read these

- **`GLOSSARY-MAP.md`** at the repo root: it points at one `GLOSSARY.md` per context (one per `lib/<Package>/`). Read each one relevant to the topic.
- **`docs/adr/`**: system-wide ADRs. Read those that touch the area you're about to work in. Also check `lib/<Package>/docs/adr/` for package-scoped decisions.

If any of these files don't exist, **proceed silently**. Don't flag their absence; don't suggest creating them upfront. The `/domain-modeling` skill (reached via `/grill-with-docs` and `/improve-codebase-architecture`) creates them lazily when terms or decisions actually get resolved.

## File structure

This is a multi-context monorepo: each package under `lib/` is a context.

```
/
├── GLOSSARY-MAP.md
├── docs/adr/                          ← system-wide decisions
└── lib/
    ├── RoadizCoreBundle/
    │   ├── GLOSSARY.md
    │   └── docs/adr/                  ← package-specific decisions
    ├── Documents/
    │   ├── GLOSSARY.md
    │   └── docs/adr/
    └── …
```

A decision spanning several packages (e.g. `Models` + `RoadizCoreBundle` + `RoadizRozierBundle`) goes in the root `docs/adr/`.

## Use the glossary's vocabulary

When your output names a domain concept (in an issue title, a refactor proposal, a hypothesis, a test name), use the term as defined in the relevant `GLOSSARY.md`. Don't drift to synonyms the glossary explicitly avoids.

If the concept you need isn't in the glossary yet, that's a signal: either you're inventing language the project doesn't use (reconsider) or there's a real gap (note it for `/domain-modeling`).

## Flag ADR conflicts

If your output contradicts an existing ADR, surface it explicitly rather than silently overriding:

> _Contradicts ADR-0001 (split File from Document), but worth reopening because…_
