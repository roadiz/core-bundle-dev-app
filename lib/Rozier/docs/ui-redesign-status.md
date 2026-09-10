# Status report — Rozier UI redesign

> One-off status document, produced on 2026-09-10 from the GitHub board
> [`orgs/roadiz/projects/1`](https://github.com/orgs/roadiz/projects/1), the Git history of
> `develop`, a component inventory of `lib/Rozier`, and a sample of Figma mockups.
> Refresh if reused later: issue, PR, and branch statuses change fast.
>
> **Corrected on 2026-09-10**: the first version listed `feature/sortable-table` and
> `feature/blanchette-editor-and-modal` as unmerged branches. On verification, both branches had
> actually already been merged in early February 2026 (PR [#383](https://github.com/roadiz/core-bundle-dev-app/pull/383) and
> [#389](https://github.com/roadiz/core-bundle-dev-app/pull/389)) and then deleted — the error came from stale local Git refs
> (`origin/*` never pruned after a `git fetch` without `--prune`). No work was lost. Section
> corrected further down with the real 5 remaining branches, which turn out to already be **open PRs**.

## Executive summary

- **v2.7 has shipped and is closed**: all 36 tickets in the milestone are `Done` on the board.
- The bulk of the visual redesign landed in a single merge (`#279`, 2026-02-02), followed by a month
  of active polish until **March 3-4, 2026**. **The effort has been stalled since** — only maintenance
  commits unrelated to design have landed since then.
- **5 open pull requests contain unmerged work** (between 1 and 15 commits each), only one of which
  (#396) is ready without being a draft, and one (#398) has conflicts.
- **v2.8** (the post-redesign backlog) has been neither prioritized nor started: 24 tickets on the
  board, all `(no status)` except a single `Todo`.
- The board and the real GitHub `v2.8` milestone have diverged: **13 open issues in the milestone
  aren't even on the board**, including 2 duplicate pairs.
- A code inventory (see below) shows that **49 of Rozier's 67 UI files are already redesigned**,
  but that Rozier runs on **Vue 2.7** (not Vue 3 as `CLAUDE.md` states) — a point that directly
  weighs on `roadmap.md`'s ambition to remove Vue.

## What has shipped (v2.7)

The `v2.7` milestone (initial due date: 2025-11-30) is 100% complete (36/36 tickets `Done`).

The core of the redesign landed in `develop` via a single squash commit:

> `024c2d9e feat: brand new UI (#279)` — 2026-02-02, 801 files changed
> (+33,788 / -18,453 lines), scope `lib/Rozier` + `lib/RoadizRozierBundle`.

This commit aggregates a long-lived integration branch `2.7-ui` (deleted since) which itself
gathered about ten sub-branches (`feat/rz-header`, `feat/rz-tooltip`, `style/rz-form`,
`feature/repeatable-widget`, `feature/rz-login-page`, etc.) built between autumn 2025 and
January 2026.

Other notable deliverables from the same milestone, visible on the board:
- Storybook (setup + color palette, [#203](https://github.com/roadiz/core-bundle-dev-app/issues/203))
- Color scheme & theming ([#232](https://github.com/roadiz/core-bundle-dev-app/issues/232))
- New `rz-tablist` component replacing the navbar ([#298](https://github.com/roadiz/core-bundle-dev-app/pull/298))
- New `rz-input` component replacing checkboxes ([#300](https://github.com/roadiz/core-bundle-dev-app/pull/300))
- Form redesign ([#295](https://github.com/roadiz/core-bundle-dev-app/pull/295), [#362](https://github.com/roadiz/core-bundle-dev-app/pull/362))
- Deptrac setup ([#275](https://github.com/roadiz/core-bundle-dev-app/pull/275))

**Confirmed scope**: strictly `lib/Rozier` (Vue/TS front) and `lib/RoadizRozierBundle`
(Twig templates + compiled assets). No trace of work on the public Nuxt starter or on shared
design tokens across repos — that's out of scope for this redesign.

## Timeline of the stall

| Date | Event |
|---|---|
| Autumn 2025 – Jan. 2026 | Development on individual sub-branches (`feat/rz-header`, `feat/rz-tooltip`, `style/rz-form`…), integrated progressively into `2.7-ui`. |
| 2026-02-02 | Squash merge `024c2d9e feat: brand new UI (#279)` into `develop`. |
| February – March 3/4, 2026 | Active polish: `RzAside`, `rz-tree`, `rzBulkActions`, `rz-table` styling, icons, draggable action menu, drawer, tooltip. |
| Since March 2026 | **Silence on design.** Only unrelated maintenance commits have landed on `lib/Rozier`: `postcss-pxtorem` (04/15), document adjustments (04/27-29), minor Vue fix on `DocumentPreviewListItem` (05/29). |

## Unmerged work (5 open pull requests)

The 5 remaining branches are in fact already proposed as PRs against `develop` — this isn't work to
"recover" but PRs to review and merge. All pass CI (`SUCCESS`) and are `MERGEABLE` except one.

| PR | Title | Created | Size | Status |
|---|---|---|---|---|
| [#416](https://github.com/roadiz/core-bundle-dev-app/pull/416) | fix(ExplorerStoreModule): remove value for filters to correct load more | 2026-03-27 | 1 file, ±1 line | Ready, mergeable — **near-zero risk** |
| [#415](https://github.com/roadiz/core-bundle-dev-app/pull/415) | style: improved style | 2026-03-27 | 4 files, +19/-2 | Draft, mergeable, CI green |
| [#417](https://github.com/roadiz/core-bundle-dev-app/pull/417) | feat: add functionality to close popover on button click in contextual menu | 2026-03-27 | 2 files, +61/-27 | Draft, mergeable, CI green |
| [#396](https://github.com/roadiz/core-bundle-dev-app/pull/396) | Fix: refresh RzAside tree depending on page tree action | 2026-02-12 | 24 files, +406/-662 | Ready, mergeable, CI green, **stale ~7 months** — retest before merging |
| [#398](https://github.com/roadiz/core-bundle-dev-app/pull/398) | feat: add rzActionMenu user drag feature | 2026-02-13 | 3 files, +68/-5 | Draft, **CONFLICTING** — conflicts to resolve. An earlier PR (#377) on the same topic was closed; check it before resuming #398 to avoid duplicating work. |

None of the 5 has a recorded review yet (`reviewDecision` empty).

**Recommendation**: merge #416 right away (zero risk), then #415/#417 after a quick review pass
(small diffs, CI green), then retest #396 before merging (the largest diff and the oldest one),
and handle #398 last (conflict resolution + check the link with #377).

## Remaining backlog (v2.8 milestone)

24 tickets currently sit on the board under `v2.8`, all not started. Grouped by theme:

**Dark mode / contrast** (the most recurring topic — 5 tickets, one of them a duplicate)
- [#436](https://github.com/roadiz/core-bundle-dev-app/issues/436) — Too much contrast/visible borders in dark mode
- [#445](https://github.com/roadiz/core-bundle-dev-app/issues/445) — Misleading contrast between page background and controls (looks disabled)
- [#446](https://github.com/roadiz/core-bundle-dev-app/issues/446) / [#447](https://github.com/roadiz/core-bundle-dev-app/issues/447) *(duplicate)* — Font colors/sizes and background hard to read
- [#448](https://github.com/roadiz/core-bundle-dev-app/issues/448) — Layout perceived as more cluttered and less legible
- [#449](https://github.com/roadiz/core-bundle-dev-app/issues/449) — Insufficient contrast on action menu / content in dark mode

**Form components**
- [#435](https://github.com/roadiz/core-bundle-dev-app/issues/435) — Boolean fields too tall, optimize vertical space
- [#438](https://github.com/roadiz/core-bundle-dev-app/issues/438) — Boolean switch: `false` state looks identical to `disabled`

**Markdown editor**
- [#443](https://github.com/roadiz/core-bundle-dev-app/issues/443) — Broken preview, needs a real fullscreen mode with live preview
- [#444](https://github.com/roadiz/core-bundle-dev-app/issues/444) — Lost syntax highlighting

**Navigation / layout**
- [#437](https://github.com/roadiz/core-bundle-dev-app/issues/437) — Main loader hasn't been updated for the new layout, randomly positioned
- [#441](https://github.com/roadiz/core-bundle-dev-app/issues/441) — Action menu overlaps the main content panel
- [#469](https://github.com/roadiz/core-bundle-dev-app/issues/469) — Keep multiple trees (node/folder/tag) accessible at the same time

**Content editing page (node-source)**
- [#434](https://github.com/roadiz/core-bundle-dev-app/issues/434) — No disclaimer when editing on a different version
- [#442](https://github.com/roadiz/core-bundle-dev-app/issues/442) — Title/publication date grid ratio should move from 50/50 to 66/33

**Technical foundations**
- [#205](https://github.com/roadiz/core-bundle-dev-app/issues/205) — [Vite] use a dedicated CSS entry point
- [#218](https://github.com/roadiz/core-bundle-dev-app/issues/218) — `MutationObserver` to initialize behaviour-type JS on DOM changes
- [#230](https://github.com/roadiz/core-bundle-dev-app/issues/230) — Remove Rozier's global config
- [#248](https://github.com/roadiz/core-bundle-dev-app/issues/248) — Status icons (integration)
- [#255](https://github.com/roadiz/core-bundle-dev-app/issues/255) — Embedded backoffice via iframe query-param
- [#332](https://github.com/roadiz/core-bundle-dev-app/issues/332) — Migrate the OpenID bundle to native Symfony OIDC authentication

**Same milestone but outside the visual redesign, absent from the board** (see next section)
- [#128](https://github.com/roadiz/core-bundle-dev-app/issues/128), [#134](https://github.com/roadiz/core-bundle-dev-app/issues/134), [#171](https://github.com/roadiz/core-bundle-dev-app/issues/171), [#409](https://github.com/roadiz/core-bundle-dev-app/issues/409)

## Board / milestone hygiene

The board (24 `v2.8` items) and the real GitHub `v2.8` milestone (31 issues) have diverged:
**13 open issues in the milestone aren't on the board**.

- **Duplicates to resolve**:
  - [#439](https://github.com/roadiz/core-bundle-dev-app/issues/439) / [#440](https://github.com/roadiz/core-bundle-dev-app/issues/440) — same title, custom-form menu icon
  - [#446](https://github.com/roadiz/core-bundle-dev-app/issues/446) / [#447](https://github.com/roadiz/core-bundle-dev-app/issues/447) — same title, UI contrast (already listed above)
  - [roadiz/roadiz#415](https://github.com/roadiz/roadiz/issues/415) / [core-bundle-dev-app#462](https://github.com/roadiz/core-bundle-dev-app/issues/462) — same bug (uploader drag-and-drop zone) reported in two different repos of the org; the board aggregates several repos, `#415` here has nothing to do with the `core-bundle-dev-app` PR `#415` cited above (a plain numbering collision across repos). Both are already on the board, without a milestone.
- **Issues related to the redesign but never triaged onto the board**: #128, #134, #171, #409 (listed above).
- **Old backend tickets unrelated to the visual redesign**, in the `v2.8` milestone but never triaged onto the board: [#16](https://github.com/roadiz/core-bundle-dev-app/issues/16), [#393](https://github.com/roadiz/core-bundle-dev-app/issues/393), [#399](https://github.com/roadiz/core-bundle-dev-app/issues/399), [#406](https://github.com/roadiz/core-bundle-dev-app/issues/406), [#428](https://github.com/roadiz/core-bundle-dev-app/issues/428), [#457](https://github.com/roadiz/core-bundle-dev-app/issues/457), [#470](https://github.com/roadiz/core-bundle-dev-app/issues/470) — probably to be taken out of the milestone rather than handled in this effort.
- `v2.8` has **no due date** (`due_on: null`), unlike `v2.7`.

This document only lists these gaps — no action has been taken on GitHub at this stage.

## Component inventory (`lib/Rozier`)

Inventory carried out on 2026-09-10 (67 tracked UI files: 21 Vue components in `app/components/`
and `app/containers/`, 38 CustomElements in `app/custom-elements/`, auto-registered via
`import.meta.glob`). Dating cross-checked between `git show --name-status 024c2d9e` (exact file
list of the redesign commit) and each file's full history, to avoid being misled by merge commits
that would skew `git log -1`.

**Overall tally: 49 redesigned files / 18 legacy** (untouched since before 2026-02-02).

| Functional area | Total | Redesigned | Legacy | Storybook story |
|---|---|---|---|---|
| Tables/listings/bulk actions (`RzTable`, `RzBulkActions`, `RzActionsMenu`) | 4 | 4 | 0 | 2/4 |
| Content editing page / node-source (`RzInput`, `RzFormField`, `RzMarkdownEditor`, `RzRepeatable`...) | ~14 | ~13 | 0 net | 7+/14 |
| Navigation/tree (`RzAside`, `RzTree`, `RzHeader*`) | 9 | 8 | 1 (`AdminMenuNav.js`) | 4/9 |
| Media library/documents (`RzFileUpload`, `Blanchette*`) | 8 | 7 | 1 (`DocumentAlignmentWidget.js`) | 2/8 |
| Cross-cutting dialogs/overlays (`RzDialog`, `RzPopover`, `RzTooltip`...) | 13 | 7 | 6 | 7/13 |
| Explorer/Drawer (related-entity picker) | 9 | 6 | 3 (incl. `NodeTypesDrawerContainer.vue`, still heavily UIkit despite being touched by the squash commit) | 2/9 |
| Tags/Folders | 6 | 1 | 5 | 0/6 |
| Login/search | 2 | 1 | 1 | 2/2 |
| Custom forms | 1 | 0 | 1 | 0/1 |
| User management | 0 dedicated component (Symfony/Twig + legacy `assets/less/users/`) | — | — | — |

**Quantified technical debt** (relevant to `roadmap.md`):
- **19 files** still use UIkit classes (`uk-*`), including some already "redesigned" files
  (e.g. `custom-elements/RzAside.ts`, `RzEntityThumbnail.ts`, `RzMarkdownEditor.ts`).
- **5 files** still use jQuery directly; jQuery/jQuery UI/UIkit remain loaded globally on every
  page via `main.js`.
- The global `window.Rozier` singleton (targeted by `roadmap.md`) is still used by ~15 files,
  including already-redesigned components.
- 78 legacy `.less` files (including all vendored UIkit) vs. 54 `assets/css/components/rz-*.css`
  files from the new design system.
- **Notable case**: `components/RzButton.vue` (legacy, `uk-button` markup) is still imported
  alongside the real redesigned `custom-elements/RzButton.ts` — a naming collision to clean up.
- **Acknowledged methodological uncertainty**: `custom-elements/RzSelect.ts` has CSS updated by the
  redesign but JS logic unchanged since before the project — visually current, architecturally
  legacy. This kind of case can't be settled by Git analysis alone; a visual check per zone remains
  necessary (see TODO).

Storybook documents 24 of the 67 files (~36%, ~49% of the redesigned ones, **0% of the legacy
ones**): a good indicator of coverage per zone, but not a complete functional inventory on its own.

Full detail lives in the conversation history that produced this document; re-audit if this file
is reused several weeks later.

## Cross-check against Figma mockups (sample)

Comparison made on 3 sections of the Figma file [`Roadiz - V3.0`](https://www.figma.com/design/RS9Difo5w26fBkQRLG7UAt/Roadiz---V3.0)
provided by the user (a sample, not exhaustive coverage of the file):

- **"Markdown" section**: the mockup already contains a **"MarkdownQuickView"** panel (preview shown
  next to the edit field, not a fullscreen modal). This directly answers
  [#443](https://github.com/roadiz/core-bundle-dev-app/issues/443) ("needs a real fullscreen mode with preview"): this isn't a design
  question to settle, it's an implementation gap — the design already exists.
- **"Structure" section** (content editing page): contains an **"Edit / FloatingBar"** component
  already specified across several states (links, repeatable, map). It matches the feature of PR
  [#398](https://github.com/roadiz/core-bundle-dev-app/pull/398) (`feature/rz-action-menu-dragging`, currently conflicting) — here too the
  design is ready, only the implementation is left to finish.
- **"Dashboard" section**: light theme only in this sample.
- **No dark mode variant appears in the 3 sections examined.** Deliberately left unresolved — to
  be checked directly in Figma (look for a dedicated dark mode section or variants) before
  concluding that dark mode isn't mocked up. Don't guess.

The Figma file has only one top-level page ("Cover") but organizes content into large "sections"
positioned on the same canvas (Dashboard, Markdown, Structure...) — an exhaustive inventory of
sections would require browsing the file directly in Figma rather than guessing node IDs.

## Legacy technical debt: UIkit, Vue, global JS — detailed status

`lib/Rozier/docs/roadmap.md` aims, in the long run, to remove `Rozier.js`, `Lazyload.js`, jQuery,
UIkit, and **Vue**, in favor of native `CustomElements`. Investigation carried out on 2026-09-10
(3 independent explorations) to check whether it's worth tackling this as soon as work resumes.
**Main finding: the redesign has already done most of the extraction work without `roadmap.md`
being updated** — these are no longer 3 big efforts to start, but mostly cleanup of already-dead
code, plus a small number of genuinely coupled blocks.

### UIkit (2.27.4 — the 2.x branch, which depends on jQuery)

- Of the ~19-22 files with `uk-*` classes, the large majority is **CSS/naming only, with no JS
  runtime**. No hardcoded `UIkit.modal()`/`UIkit.dropdown()` call was found anywhere.
- **UIkit JS behaviours that are genuinely dead** (imported in `main.js`/`vendor.less` but with no
  markup or handler left to trigger them): `switcher` (referenced in vain by 4 files:
  `RzMarkdownEditor.ts`, `YamlEditor.js`, `JsonEditor.js`, `CssEditor.js`), `sortable` (the target
  template was rebuilt without drag-and-drop), `nestable`, `datepicker`, `pagination`, `notify`,
  `htmleditor` — removable without reimplementation. Plus **12 orphaned vendored CSS files**
  (never imported).
- **JS behaviours genuinely still alive to handle: only 2** — the dismissible alert
  (`data-uk-alert` × 8 templates, no dedicated native equivalent yet) and the legacy tooltip
  (`data-uk-tooltip` × 8, but `RzTooltip.ts` already exists and just needs to be wired up).
- Native equivalents already in place and adopted: `RzDialog` (replaces `uk-modal`), `RzPopover`
  (replaces `uk-dropdown`, already used in 10 templates), `RzTablist`, `RzDrawer`, `RzToastList`.
- 4 files have a double dependency (native + UIkit leftover) to clean up:
  `RzEntityThumbnail.ts`, `RzMarkdownEditor.ts`, `RzAside.ts`, `base.html.twig`.

### Vue (2.7.16, EOL since late 2023 — not Vue 3 as `CLAUDE.md` states, to be fixed)

21 `.vue` files. Mounted via 3 mechanisms in `App.js`/`main.js`. State management: Vuex 3.0.1,
6 modules, **100% confined to the Vue world** (no custom element imports the store).

- **6 files are already dead** (not mounted, not imported through any live path): `DrawerContainer.vue`
  (replaced by `<rz-drawer>` in the same redesign commit), `NodeTypesDrawerContainer.vue`,
  `TagsEditorContainer.vue`, `RzButton.vue` (naming collision with the native `RzButton.ts`),
  `RzTextarea.vue`, `CodeMirror.vue`. Directly removable.
- **7 files are isolated and already follow the event-driven pattern** proven on `rz-drawer`
  (`CustomEvent` on `window`/`document`, no Vue/custom-element nesting): `Overlay.vue`,
  `BlanchetteEditorContainer.vue` + `BlanchetteToolbar.vue` and their leaf children. Migratable
  file by file, with low risk.
- **8 files form a genuinely coupled block** through Vuex and a shared mount: `ExplorerContainer.vue`,
  `FilterExplorerContainer.vue`, `DocumentPreviewContainer.vue`, `ModalContainer.vue` and their
  dynamic children. This is the only real remaining "big effort" on Vue — it requires replacing
  the Vuex store, not just porting templates.
- **No tests at all** (no vitest/jest, Storybook structurally unable to render Vue 2).

### Global JS / `window.Rozier`

- `Rozier.js` (1095 lines) was already rewritten as `Rozier.ts` (141 lines) during the redesign:
  almost all of the tree logic was extracted into `RzAside.ts`/`RzTree.ts`. Only ~10 files still
  consume `window.Rozier`, each with an isolated, shallow call (reading messages, delegating to
  `<rz-aside>`) — no remaining structural coupling.
- **Bug found while verifying this finding**: [`StackNodeTree.js:194`](../app/widgets/StackNodeTree.js#L194)
  calls `window.Rozier.initNestables()`, a method that no longer exists on the current `Rozier`
  class (confirmed by reading the whole of `Rozier.ts`) → `TypeError` on every drag-and-drop tree
  reorder, which also prevents the following lines from running (`bindMainTrees()`,
  `lazyload.bindAjaxLink()`, `resize()`). **To be fixed independently of any removal effort**,
  likely an active user-facing bug. `Lazyload.ts` also contains fields that are never reassigned
  (dead code to clean up: `inputLengthWatcher`, `documentUploader`, `geotagField`, `multiGeotagField`, `tagAutocomplete`).
- **jQuery is coupled to UIkit, not just to the 5 files that use it directly**: UIkit 2.27.4
  requires `window.jQuery` at runtime for its imported components (confirmed by an explicit
  comment in the code: *"HERE WE NEED JQUERY BECAUSE UI-KIT V2 REQUIRE JQUERY"*), invisible in the
  npm dependency graph. **jQuery can't be removed without removing UIkit first** (or both at once).
- `main.js` loads jQuery/UIkit/jQuery UI/Rozier in a blocking way on **every** authenticated page,
  whether the current page uses them or not.

### Verdict: worth tackling as soon as work resumes, or defer?

Not a binary choice per topic — there are 3 mixed levels of effort across these 3 topics:

1. **Near-free cleanup, to do right away (Phase 0)**: remove the 6 dead Vue files, the dead
   UIkit behaviours/imports (switcher, sortable, nestable, datepicker, pagination, notify,
   htmleditor) and the 12 orphaned vendored CSS files, fix the `initNestables()` bug, clean up the
   dead fields in `Lazyload.ts`. Very low risk, immediate readability gain, and it shrinks the
   surface before starting on the visual zones.
2. **Small, contained tasks, to fold into the relevant zones**: wire up `RzTooltip.ts` for the 8
   remaining `data-uk-tooltip` usages, handle the 4 double-dependency files, migrate the 7 isolated
   Vue files as their zone comes up (e.g. `BlanchetteEditorContainer` when working on the media
   library).
3. **The only real remaining "big effort"**: the 8-file Vue+Vuex block (Explorer/FilterExplorer/
   DocumentPreview/Modal) + the actual removal of jQuery/UIkit from the bundle (`main.js`,
   `vendor.less`, the dismissible alert with no native equivalent). **Not urgent for resuming the
   visual redesign**, but worth planning as a dedicated phase after 1-2 visual zones have been
   stabilized (to rebuild team rhythm and a bit of a safety net before tackling the riskiest part).
   With no automated tests anywhere in `lib/Rozier`, plan a manual QA checklist per removed
   behaviour rather than relying on a test suite that doesn't exist.

In short: don't treat "remove UIkit / Vue / global JS" as 3 separate fronts to weigh against the
visual redesign. Part 1 should happen right away (near-free), part 2 happens along the way as
zones are worked on, and only part 3 is a genuine planning trade-off — see
[`ui-redesign-todo.md`](ui-redesign-todo.md) for where it sits in the phases.

## Proposed roadmap to resume work

See [`ui-redesign-todo.md`](ui-redesign-todo.md) — a phased, actionable methodology and TODO for
resuming work, built from this status report, the component inventory, and the Figma sample above.
