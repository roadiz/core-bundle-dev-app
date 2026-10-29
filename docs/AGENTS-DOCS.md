# Roadiz Documentation Agent Guidelines

Purpose
- Guide agents specialized in writing and editing Roadiz documentation.
- Ensure consistency in style, structure, and quality across VitePress docs and code documentation.

## Documentation Overview

The documentation lives in `docs/` and is built with VitePress.
- Published at: https://docs.roadiz.io/ (develop branch: https://docs.roadiz.io/develop/)
- `docs/user/`: User documentation (**French**).
- `docs/developer/`: Developer documentation (**English**).
- `docs/extensions/`: Extension and customization guides (English).
- `docs/.vitepress/config.mts`: VitePress configuration and sidebar navigation.

## Build and Preview Commands

Local development:
```shell
cd docs && corepack enable && pnpm install && pnpm docs:dev
```

Docker: `docker compose up vitepress`

Build: `pnpm docs:build` (or `VITE_CONFIG_BASE=develop pnpm docs:build` for develop branch)

## Adding New Documentation Pages

1. Create markdown file in appropriate section (`docs/user/`, `docs/developer/<category>/`, `docs/extensions/`).
2. Update sidebar in `docs/.vitepress/config.mts`: add `{ text: 'Title', link: '/path/to/page' }`.
3. Preview locally before committing.

## Markdown Style Guidelines

Frontmatter (optional):
```yaml
---
title: Page Title
---
```

Headings:
- `# H1` for main title (one per page), `## H2` for sections, `### H3` for subsections.
- Keep headings concise; avoid nesting beyond H3.

Code blocks:
- Specify language: ```php, ```shell, ```yaml, ```twig, ```typescript.
- Use `shell` for CLI examples. Keep examples minimal.

Images:
- Store in `img/` subfolder next to markdown: `![alt](./img/file.webp)`.
- Prefer `.webp` format; add meaningful alt text.

Links:
- Internal: `[text](/developer/api/intro)` or `[text](/page#section)`.
- External: absolute URLs.

Tables: use markdown tables for feature grids with `|---|` separators.

## Language and Tone

User docs (`docs/user/`):
- **French**, formal ("vous"), practical how-to focus, screenshots welcome.

Developer docs (`docs/developer/`, `docs/extensions/`):
- **English**, technical language, include code examples, link to Symfony/API Platform docs.

General: professional, concise, active voice, imperative for instructions. Explain "why" alongside "how".

## Code Documentation (PHPDoc)

Class documentation:
```php
/**
 * Brief description of the class purpose.
 *
 * Longer description if needed.
 */
```

Method documentation:
```php
/**
 * Brief description of method.
 *
 * @param string $param Description
 * @return bool Description
 * @throws \InvalidArgumentException When input invalid
 */
```

Guidelines:
- Document all public methods/properties.
- Use `@inheritDoc` for overrides; avoid redundant descriptions.
- Include `@throws` for caller-relevant exceptions.
- Use generics: `Collection<int, NodesSources>`.

Inline comments: explain "why" not "what"; use `//` for complex logic only.

## Bundle README Files

Each `lib/` bundle should have README.md with:
- Brief description and purpose.
- Installation: `composer require roadiz/...`.
- Basic configuration.
- Link to full docs.

Keep READMEs concise; detailed content belongs in `docs/`.

## Quality Checklist

Before submitting:
- [ ] Preview with `pnpm docs:dev`.
- [ ] Check spelling, grammar, links.
- [ ] Verify code examples are correct.
- [ ] Update sidebar if adding pages.

Terminology:
- "node" (not "page"), "node-type" (hyphenated), "back office" (two words).
- "Roadiz" (capitalized), "Rozier" (admin theme).

## VitePress Features

Custom containers (use sparingly):
```markdown
::: tip
Helpful tip.
:::

::: warning
Warning content.
:::

::: danger
Critical warning.
:::
```

Features enabled: local search, GitHub edit links, last-updated timestamps.

## Notes for Documentation Agents

- Prioritize clarity and accuracy over completeness.
- Add examples from real code when documenting features.
- Cross-reference related pages.
- Keep sidebar hierarchy shallow (max 2 levels).
- Respect French/English language split strictly.
- Verify technical accuracy against codebase.
- Always preview changes with `pnpm docs:dev`.
