---
status: accepted
---

# Build Rozier assets at split time, never commit them

The backoffice assets built from `lib/Rozier` into `lib/RoadizRozierBundle/public/` used to be committed by hand. Nothing enforced the build, so the `roadiz/rozier-bundle` split shipped whatever was last committed: on `develop`, a project running `composer update` got PHP and Twig code from today with a front-end weeks behind it, and every release depended on someone remembering to build. The assets are therefore gitignored in the monorepo and built by the `split.yaml` workflow, on the `RoadizRozierBundle` matrix leg only, right before splitting `develop`, `main` and tags. Only the split repository holds built assets.

We rejected a bot committing the build back to the monorepo after each merge (it needs push rights past the branch protection and doubles every front-end merge with a build commit), a CI check failing PRs whose committed build is stale (two front-end PRs in flight always conflict on the hashed filenames) and a local pre-commit hook (it depends on each machine and does not cover merges).

## Consequences

- The ignore rule lives in the root `.gitignore`, never in `lib/RoadizRozierBundle/.gitignore`. The split action copies the package directory, its `.gitignore` included, then runs `git add .`: a rule there would drop the assets from the split.
- A fresh clone has no backoffice assets until `make build_assets` runs (or the Vite dev server, `docker compose up node`). Tests stub the manifest (`run-test.yml`).
- A failed build fails only the `rozier-bundle` split, which keeps its previous version; other packages still split (`fail-fast: false`). `build-assets.yml` builds every PR, so such a failure should not reach a split.
- The split history of `roadiz/rozier-bundle` carries the hashed asset churn; the monorepo history no longer does.
- Applies from 2.8. Maintenance branches of earlier versions keep their committed assets.
