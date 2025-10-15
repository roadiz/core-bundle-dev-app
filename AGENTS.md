# Roadiz - Agents and Git commit instructions
## Agents instructions

Purpose
- Provide concise, project-specific guidance for Copilot suggestions and generated code.
- Ensure suggestions follow repository conventions, CI checks, and packaging workflows.

GitHub Copilot specific instructions can be found in:
- [`.github/copilot-instructions.md`](.github/copilot-instructions.md)
- [`.github/git-commit-instructions.md`](.github/git-commit-instructions.md)

Project overview
- Folder structure
    - `docs/`: Roadiz documentation (VitePress)
    - `src/`: Development application sources (testing the CMS)
    - `lib/`: Symfony bundles and shared libraries. Each bundle is split into its own repository during CI and published under the `roadiz/` namespace.
        - `lib/RoadizCoreBundle`, `lib/RoadizRozierBundle`, `lib/RoadizSolrBundle`, `lib/RoadizTwoFactorBundle`, `lib/RoadizUserBundle`, `lib/RoadizFontBundle`, `lib/Documents`, `lib/DocGenerator`, `lib/DtsGenerator`, `lib/EntityGenerator`, `lib/Jwt`, `lib/Markdown`, `lib/Models`, `lib/OpenId`, `lib/Random`, `lib/Rozier`
- Key technologies
    - PHP 8.0+
    - Symfony framework
    - API Platform
    - Doctrine ORM
    - PHPUnit (tests)
    - PHPStan (static analysis)
    - PHP-CS-Fixer (code style)
    - Docker / Docker Compose
    - Crowdin (localization)

Coding and contribution rules (for Copilot)
- Language & style
    - Target PHP 8+ and follow Symfony best practices and PSR-12 coding style.
    - Keep code readable, well-documented (docblocks where appropriate), and minimal.
    - Prefer strict typing (declare types and return types).
- Tests & quality
    - Add or update PHPUnit tests for any functional change. Favour concise unit tests and integration tests where appropriate.
    - Ensure PHPStan issues are addressed; aim for existing project PHPStan level.
    - Run PHP-CS-Fixer formatting suggestions prior to proposing final patches.
- Composer & packaging
    - When touching bundles in `lib/`, keep in mind CI splits packages via `.github/workflows/split.yaml`. Avoid breaking public APIs without a migration note.
    - Do not publish packages here manually; CI handles publishing under `roadiz/`.
- Assets & frontend
    - `lib/Rozier` builds CSS/JS/images into `lib/RoadizRozierBundle/public/`. When changing frontend code, ensure build steps are described.
- CI & localization
    - Consider Docker Compose and CI pipelines when suggesting environment or devcontainer changes.
    - Use Crowdin for localization strings; avoid hardcoding user-facing text without extraction.

How Copilot should present code suggestions (required formatting)
- Always start suggestions with a short step-by-step plan describing the approach.
- Group code edits by file. For each file:
    - Use the exact file path as a header.
    - Give a one-line summary of the change.
    - Provide a single code block per file. For Markdown files, wrap the block with four backticks.
    - The first line inside the code block must be a single-line comment containing the exact filepath (for example: `// filepath: /path/to/file`).
    - Avoid repeating unchanged code. Use a single-line comment `// ...existing code...` to indicate omitted regions.
    - Minimal hints only — the repository maintainer will merge the changes.
- Example pattern to follow:
    - Step-by-step plan
    - Header: file path
    - One-line summary
    - Code block starting with `// filepath: ...`
    - Code edits and `// ...existing code...` where appropriate

Behavioural rules for generated edits
- Keep changes minimal and focused. Prefer small, incremental patches.
- Do not add unrelated dependencies or large scaffolding without explicit instruction.
- If a requested change is ambiguous, provide 2–3 concise options and ask for clarification.
- If the requested task is not about software engineering or involves harmful content, respond with: "Sorry, I can't assist with that."

Developer interaction notes
- If asked to present edits for multiple files, follow the file-grouping and single-code-block-per-file rule strictly.
- When asked to generate a patch, always include tests and static analysis notes where relevant.
- Mention any required commands to run locally (tests, PHPStan, composer install, build steps) in one line.

Contact / maintainers
- This file is intended to guide automated suggestions; maintainers may update it as project needs evolve.

## Git commit instructions

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
