# Roadiz — Git commit instructions

We use Conventional Commits as the canonical format. Keep commits small, focused and descriptive.

Format
- Commit header: type(scope): subject
  - Example: feat(api): add pagination to node list
- Subject:
  - Use imperative mood and present tense (Add, Fix, Update).
  - Keep under 50 characters.
  - Do not capitalize first word; do not end with a period.
- Body (optional but recommended for non-trivial changes):
  - Separate header and body with a blank line.
  - Wrap lines at ~72 characters.
  - Explain the motivation and contrast with previous behavior if relevant.
- Footer (optional):
  - Used for referencing issues, PRs, and breaking changes.
  - Example: Fixes #123, Refs #234
  - Breaking change format: BREAKING CHANGE: A short description of the change and migration steps.

Allowed types
- feat: a new feature
- fix: a bug fix
- docs: documentation only changes
- style: formatting, missing semi-colons, etc. (no code changes)
- refactor: code change that neither fixes a bug nor adds a feature
- perf: code change that improves performance
- test: adding or updating tests
- chore: build process or auxiliary tool changes
- ci: CI configuration and scripts

Scope
- Optional, but helpful to indicate area affected: e.g., api, ui, auth, db
- Use lowercase, single-word scopes when possible.

Examples
- feat(api): support sorting by title
- fix(auth): sanitize input to prevent NullPointer
- docs: update README with setup steps
- chore(ci): add PHPStan to pipeline
- refactor(core): split service into two for clarity

Breaking changes
- Use the `BREAKING CHANGE:` footer to describe what changed and how to migrate.
- Include a short migration example when appropriate.

Trailers and metadata
- Add `Signed-off-by: Name <email>` or use GPG signing (`git commit -S`) if required by the repository.
- Use `Co-authored-by: Name <email>` when multiple authors contributed.
- Reference issues/PRs in the footer: `Fixes #123`, `Refs #456`.

Checks and tooling
- Aim to run tests and static analysis before committing when practical.
- Recommended commands to run locally (one-liners):
  - composer install && vendor/bin/phpunit
  - vendor/bin/phpstan analyse
  - vendor/bin/php-cs-fixer fix --dry-run --diff
- Consider using commit hooks / commitlint to enforce format.

Notes
- Keep commits atomic. If a change affects multiple unrelated areas, split it into multiple commits.
- If unsure, provide a concise commit and open a PR description with more context.
