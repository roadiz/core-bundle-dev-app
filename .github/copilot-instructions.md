# Roadiz — Copilot instructions

Purpose
- Provide concise, project-specific guidance for Copilot suggestions and generated code.
- Ensure suggestions follow repository conventions, CI checks, and packaging workflows.

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
