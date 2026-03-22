# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

> For other AI agents and models, see [AGENTS.md](../AGENTS.md) which covers the same project with broader compatibility.

## Project Overview

This is a **Symfony 7.4 monorepo** serving as the development environment for Roadiz CMS v2. The `lib/` directory contains 11+ independent packages that are split into separate Git repositories via CI. The `src/` directory is the dev application itself (not a published package).

PHP 8.3+ is required. The stack includes Doctrine ORM 2.20, API Platform 4.1, PHPUnit 9.6, PHPStan level 8, and Symfony Messenger with Redis.

## Commands

All development commands assume Docker Compose is running. Use `make bash` to get a shell in the app container.

**Testing:**
```bash
make phpunit                          # Full PHPUnit suite (MariaDB + MySQL)
docker compose exec app vendor/bin/phpunit tests/SomeTest.php   # Single file
docker compose exec app vendor/bin/phpunit --filter SomeTest    # By name
```

**Static analysis & linting:**
```bash
make phpstan          # PHPStan at level 8
make check            # PHP-CS-Fixer check (no fix)
make rector_test      # Rector dry run
make check-architecture  # Deptrac layering rules
```

**Fix code style:**
```bash
make rector           # Rector + PHP-CS-Fixer fix
docker compose run --no-deps --rm --entrypoint= app vendor/bin/php-cs-fixer fix --ansi -vvv
```

**Full CI check:**
```bash
make test             # Monorepo validation, audit, phpstan, rector, deptrac, twig lint, phpunit
```

**Infrastructure:**
```bash
make cache            # Clear caches, restart workers
make migrate          # Interactive migrations
make update           # Non-interactive migrations + fixtures
```

**Frontend (Rozier admin UI):**
```bash
docker compose up node                    # Dev server (HMR on port 5173)
docker compose run --rm node pnpm build   # Build assets
# Local: cd lib/Rozier && corepack install && pnpm install --frozen-lockfile && pnpm dev
```

## Architecture

### Monorepo Package Layout

```
lib/
  RoadizCoreBundle/   # Core CMS bundle (base layer)
  RoadizRozierBundle/ # Admin UI Symfony bundle
  RoadizUserBundle/   # User management
  RoadizTwoFactorBundle/
  RoadizSolrBundle/
  Rozier/             # Frontend source (Vue 3 + Vite, pnpm)
  Models/             # Shared domain models
  Documents/          # Document/media handling
  Jwt/                # JWT utilities
  OpenId/             # OpenID Connect
  Markdown/           # Markdown processing
  DocGenerator/       # Auto-documentation
  DtsGenerator/       # TypeScript definitions
  EntityGenerator/    # Doctrine entity generator
  Random/             # Random utilities
src/                  # Dev app (not published)
tests/                # App-level tests
```

**Deptrac enforces strict layering:** `Models` has no internal deps; `RoadizCoreBundle` depends on `Models`; `RoadizRozierBundle` depends on `RoadizCoreBundle`. Run `make check-architecture` after adding cross-bundle dependencies.

### Namespace Mapping

```
RZ\Roadiz\*         → lib/*/src/
RZ\Roadiz\Tests\*   → lib/*/tests/
App\                → src/
App\Tests\          → tests/
```

### Key Architectural Patterns

- **Controllers are thin** — business logic lives in services/handlers.
- **Entities hold state only** — heavy logic goes in services.
- **Messages/Commands** use Symfony Messenger for async work.
- **API resources** use API Platform with custom state providers/processors in `src/Api/` and `lib/RoadizCoreBundle/src/Api/`.
- **Generated entities** (`src/GeneratedEntity/`) are auto-generated — do not edit manually.
- **Frontend assets** in `lib/RoadizRozierBundle/public/` are build artifacts — edit source in `lib/Rozier/` instead.

### Test Setup

`tests/bootstrap.php` auto-creates the test database and runs migrations. PHPUnit runs against both MariaDB 11.8 and MySQL 8.4 in CI (matrix). Tests in `phpunit.xml.dist` cover `tests/`, `lib/Models/tests/`, `lib/RoadizCoreBundle/tests/`, `lib/Documents/tests/`, and `lib/EntityGenerator/tests/`.

## Code Style

- Always `declare(strict_types=1);`
- Typed properties and explicit return types everywhere; avoid `mixed`
- Constructor promotion preferred for readability
- Early returns / guard clauses to reduce nesting
- Conventional Commits format: `type(scope): subject` (imperative, ≤50 chars header)
- Allowed commit types: `feat`, `fix`, `docs`, `style`, `refactor`, `perf`, `test`, `chore`, `ci`

## Monorepo Tooling

When changing dependencies across bundles:
```bash
vendor/bin/monorepo-builder validate   # Check version alignment
vendor/bin/monorepo-builder merge      # Sync dependencies
```

PHPStan may misbehave when `lib/*` packages are symlinked — run from inside the container.

## Environment

- `.env.local` for local overrides (never commit)
- Initial setup: `bin/console install` then `bin/console app:install`
- Create admin users: `bin/console users:create`
- Docs dev server: `docker compose up vitepress` (port 5174)

## Off-limits files

**NEVER read, search, or output the contents of any of the following files** — they contain local secrets (API keys, OAuth credentials, passphrases) and must not be inspected by any tool or agent:

- `.env.local`
- `.env.*.local` (e.g. `.env.test.local`, `.env.dev.local`)
