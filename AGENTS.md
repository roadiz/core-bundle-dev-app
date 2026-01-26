# Roadiz - Agent guidelines

Purpose
- Provide project-specific guidance for automated coding agents.
- Keep suggestions aligned with CI checks, packaging workflows, and style rules.

Authoritative instructions
- Copilot rules: `.github/copilot-instructions.md`.
- Git commit rules: `.github/git-commit-instructions.md`.
- Cursor rules: none found in `.cursor/rules/` or `.cursorrules`.

Project overview
- Monorepo with Symfony bundles in `lib/` and a dev app in `src/`.
- Key tech: PHP 8.3+, Symfony 7.4, Doctrine ORM, API Platform, PHPUnit, PHPStan, PHP-CS-Fixer.
- Documentation in `docs/` (VitePress) and admin UI in `lib/Rozier` (PNPM).

Repository layout
- `src/`: dev app sources and configuration.
- `lib/*Bundle`: Symfony bundles that are split and published in CI.
- `lib/Models` and `lib/*`: shared libraries and generators.
- `tests/`: application tests; bundle tests live under each bundle.
- `docs/`: VitePress documentation site.

Build, lint, and test commands
- Install deps (Docker): `docker compose run --rm --no-deps --entrypoint= app composer install`.
- Requirements check only: `make requirements`.
- Composer audit: `make audit`.
- All checks (CI-like): `make test`.
- PHPStan: `make phpstan` (runs `vendor/bin/phpstan analyse -c phpstan.neon`).
- Coding style check: `make check` (PHP-CS-Fixer check).
- Coding style fix: `docker compose run --no-deps --rm --entrypoint= app vendor/bin/php-cs-fixer fix --ansi -vvv`.
- Rector dry run: `make rector_test`.
- Rector apply: `make rector` (also runs PHP-CS-Fixer fix).
- Architecture rules: `make check-architecture` (Deptrac).
- PHPUnit full suite: `make phpunit` (runs against MariaDB + MySQL containers).
- Twig lint: part of `make test`, or run `bin/console lint:twig` for bundle templates.
- Single test file (Docker): `docker compose exec app vendor/bin/phpunit tests/SomeTest.php`.
- Single test by name: `docker compose exec app vendor/bin/phpunit --filter SomeTest`.
- Bundle tests: use bundle paths listed in `phpunit.xml.dist` (for example `lib/RoadizCoreBundle/tests`).
- CI note: `make test` runs PHPStan, Rector, Deptrac, Twig lint, and PHPUnit.
- PHPStan can misbehave when `lib/*` bundles are symlinked.

Docker helpers
- Shell in container: `make bash`.
- Cache reset: `make cache` (clears caches and restarts workers).
- Migrations: `make migrate` (interactive) or `make update` (non-interactive).

Environment notes
- Use `.env.local` for local overrides; never commit it.
- PHPUnit uses container databases and drops them after runs.
- Run `bin/console install` then `bin/console app:install` for dev fixtures.
- Create admin users with `bin/console users:create`.
- Prefer Docker Compose for reproducible services (DB, Solr, Redis).

Frontend and docs commands
- Backoffice dev server (Docker): `docker compose up node`.
- Backoffice build (Docker): `docker compose run --rm node pnpm build`.
- Backoffice dev (local): `cd lib/Rozier && corepack install && pnpm install --frozen-lockfile && pnpm dev`.
- Docs dev server (Docker): `docker compose up vitepress`.
- Docs dev (local): `cd docs && corepack enable && pnpm install && pnpm docs:dev`.
- Frontend source lives in `lib/Rozier`; built assets go to `lib/RoadizRozierBundle/public/`.
- Avoid editing generated assets in `lib/RoadizRozierBundle/public/`.

Code style guidelines (PHP)
- Follow PSR-12 and Symfony conventions; PHP-CS-Fixer uses `@Symfony` rules.
- Always use `declare(strict_types=1);` in PHP files.
- Prefer typed properties and explicit return types; avoid mixed when possible.
- Use constructor promotion where it improves readability and keeps classes small.
- Keep service classes small and focused; prefer composition over static helpers.
- Avoid adding new dependencies unless explicitly requested.
- When touching `lib/*` bundles, avoid breaking public APIs without migration notes.
- Favor early returns and guard clauses to reduce nesting.
- Keep controllers thin; move logic into services or handlers.
- Prefer immutable value objects for request/response payloads.

Imports and namespace layout
- One namespace per file; class/trait/interface name matches filename.
- Group imports by origin (PHP, vendor, project), separated by blank lines.
- Remove unused `use` statements; prefer fully-qualified class names in annotations if needed.
- Order imports alphabetically within each group.
- Avoid global functions imports unless required for performance.

Naming conventions
- Classes, interfaces, traits: PascalCase.
- Methods and variables: camelCase.
- Constants: UPPER_SNAKE_CASE.
- Tests: `*Test` suffix; test methods start with `test` or use `@test`.
- Services: use descriptive nouns (e.g. `NodeFinder`, `TokenGenerator`).
- Events and messages: suffix with `Event`, `Message`, `Command`, or `Query`.

Error handling and logging
- Throw domain-appropriate exceptions; do not swallow exceptions silently.
- Use Symfony exceptions or custom exceptions for recoverable errors.
- Prefer `Psr\Log\LoggerInterface` for logging, keep messages actionable.
- For user-facing errors, map exceptions to translated messages.
- Avoid logging sensitive data (tokens, passwords, PII).

Security and secrets
- Never commit `.env.local`, API keys, or credentials.
- Redact sensitive values in logs, tests, and fixtures.

Symfony and Doctrine practices
- Prefer dependency injection over `ContainerAwareTrait` or static access.
- Avoid direct SQL when Doctrine or repositories provide the abstraction.
- Validate user input with Symfony Validator; avoid custom ad-hoc checks.
- Use repository methods for query reuse and clear intent.
- Keep entities focused on state, not heavy business logic.

Testing guidelines
- Add or update PHPUnit tests for functional changes.
- Keep tests focused; use data providers for repetitive cases.
- Use `tests/bootstrap.php` and existing test utilities (see `tests/`).
- Respect `phpunit.xml.dist` suites and environment variables.
- Prefer integration tests for Doctrine behavior and service wiring.
- Clean up fixtures and side effects to keep tests isolated.

Static analysis
- PHPStan runs at level 8; fix new issues instead of adding ignores.
- Use phpdoc generics where needed; avoid broad `mixed` types.
- Keep `phpstan.neon` consistent across bundles when adding rules.

Localization and user-facing text
- Avoid hardcoding UI strings; use translation keys and Crowdin workflow.
- Ensure new messages are extractable and localized.
- For Twig templates, use `trans` filters or the translator service.

Monorepo tooling
- `vendor/bin/monorepo-builder merge` for dependency syncing across bundles.
- `vendor/bin/monorepo-builder validate` to keep versions aligned.
- Update Deptrac rules when adding a new bundle layer.

Copilot formatting rules (must follow when generating suggestions)
- Start with a short step-by-step plan.
- Group edits by file with a single code block per file.
- Code blocks begin with a single-line comment containing the filepath.
- Use `// ...existing code...` to omit unchanged sections.
- Markdown blocks must use four backticks when requested by Copilot rules.

Git commit guidance (Conventional Commits)
- Format: `type(scope): subject` with imperative, lowercase subject.
- Keep header under 50 characters; use body for rationale.
- Allowed types: feat, fix, docs, style, refactor, perf, test, chore, ci.
- Use footer for issues or BREAKING CHANGE notes.
- Wrap body lines around 72 characters.

Notes for agents
- Keep changes minimal, focused, and aligned with CI tooling.
- Call out required commands to run (tests, PHPStan, lint) in one line.
- If a request is ambiguous, provide 2-3 options and ask for clarification.
- Avoid editing generated assets in `lib/RoadizRozierBundle/public/`.
