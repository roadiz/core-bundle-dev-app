# TODO — Methodical resumption of the Rozier redesign

> Built from a status report of 2026-09-10, whose component inventory is kept in the
> [appendix](#appendix--component-inventory-2026-09-10). Goal: give a precise framework so we don't work on several big fronts at once —
> Rozier is a large interface, spreading thin across it produces a lot of zones at 60% rather than
> finished zones.

## Methodology

**Principle: vertical slice by functional zone, not horizontal by component type.**
Finish a zone to 100% (design-compliant, dark mode validated, Storybook story, tickets closed)
before opening the next one — rather than making a bit of progress everywhere at once.

Rules to avoid spreading thin:
1. **Only one "active" zone at a time.** Any other idea that comes up goes into the backlog (v2.8/v2.9), not into the current sprint.
2. **Definition of "done" per zone**: legacy components migrated or explicitly excluded, dark mode checked on this zone, cross-check done against the matching Figma section, related backlog tickets closed, Storybook story up to date.
3. **Dark mode/contrast is cross-cutting** but only rolls out *after* being validated on a pilot zone — no unverified global fix (that's the symptom already visible in the current backlog).
4. **Figma review before each zone**: look up the matching section in the `Roadiz - V3.0` file and compare before coding, not after.
5. **Don't guess what isn't verifiable** (a Figma node ID, a branch's status, the content of an unread file) — verify instead of assuming.

## Zone order and rationale

Based on the [component inventory](#appendix--component-inventory-2026-09-10): the nearly-finished zones close quickly and
build momentum; the heavily legacy zones need a real effort, not just touch-ups.

| Order | Zone | Progress | Why this order |
|---|---|---|---|
| 1 (pilot) | Content editing page / node-source | 13/14 | Concentrates most of the open bug tickets; the Figma design for the gaps (MarkdownQuickView, FloatingBar) is already ready; the most-used zone day to day |
| 2 | Navigation / tree | 8/9 | Nearly done, 2 tickets to close out |
| 3 | Media library / documents | 7/8 | Nearly done |
| — | Tables / listings / bulk actions | 4/4 | Already done — just re-validate dark mode when it's tackled in Phase 4 |
| 4 | Cross-cutting dialogs / overlays | 7/13 | Used by every other zone — the legacy duplicates (`RzButton.vue`/`RzTextarea.vue`) were removed as dead code in #534 |
| 5 | Explorer / Drawer | 6/9 | `NodeTypesDrawerContainer.vue`, the heaviest UIkit user, was dead and removed in #534; the remaining legacy files still need migrating |
| 6 | Tags / Folders | 1/6 | The biggest gap — probably not just catch-up work, needs a real design pass before coding |
| 7 | Custom forms | 1/1 | Small footprint but never started; its only legacy script (`CustomFormFieldsPosition.js`) was replaced by the `RzTable` sortable in #537 |
| 8 | User management | 0 dedicated component | No existing foundation, the biggest remaining effort — to be scoped separately with a dedicated Figma mockup |

## Phase order

Technical work comes before design polish and new scope (decided 2026-10-30): removing Vue, Vuex, jQuery and UIkit first clears the ground every zone then builds on, instead of redesigning components that still sit on the legacy stack. Phases run in their number order. Two exceptions:

- **Regressions** against the previous release (e.g. [#537](https://github.com/roadiz/core-bundle-dev-app/issues/537)) are fixed as soon as possible, outside the phase order.
- The **pilot zone** (Phase 3) still comes before the cross-cutting contrast work (Phase 4), since it serves as the contrast reference.

## Phase 0 — Hygiene (before writing any code)

This phase is near-free (few design decisions) and makes sense right after the roadmap is
validated: it clears the ground and rebuilds some rhythm before the technical phases. **Time-box it to
2-3 days** — several items below need a quick manual check before removal (no automated tests in
`lib/Rozier`); don't let it drift into an exhaustive verification effort that delays the next phases.

**Closing it** (tracked in [#531](https://github.com/roadiz/core-bundle-dev-app/issues/531)): the phase is closed when every item is done or excluded with a written trace. One PR per item, in this order: #396, then the dead stack-tree refresh, then the Vue, UIkit and `Lazyload.ts` removals. A file counts as dead when nothing references it and a manual click-through of the related screens shows no regression; record that QA checklist in the PR.

**The order below matters**: merge the PRs first, *before* touching the legacy cleanup/bugfix work —
PR [#396](https://github.com/roadiz/core-bundle-dev-app/pull/396) directly modifies `Rozier.ts`, `RzAside.ts`, and `RzTree.ts`,
exactly the files where the stack-tree refresh and the Vue/UIkit cleanup below live. Fixing/
cleaning up before merging #396 risks a conflict or an overwritten fix.

- [x] Merge [#416](https://github.com/roadiz/core-bundle-dev-app/pull/416) (near-zero risk)
- [x] Review + merge [#415](https://github.com/roadiz/core-bundle-dev-app/pull/415) and [#417](https://github.com/roadiz/core-bundle-dev-app/pull/417)
- [x] [#532](https://github.com/roadiz/core-bundle-dev-app/issues/532) [#396](https://github.com/roadiz/core-bundle-dev-app/pull/396) merged on 2026-10-29 after merging `develop` (conflict on `RzNodeTreeContextualMenu.ts` resolved by keeping `develop`'s version) and review fixes (flash messages, main-tree refresh listeners)
- [x] ~~Merge [#398](https://github.com/roadiz/core-bundle-dev-app/pull/398)~~ closed as superseded: `develop` already ships the draggable `RzActionsMenu` (2026-10-28)

*(From here on, the code in `Rozier.ts`/`RzAside.ts`/`RzTree.ts` has moved — work from the
post-merge state, not from the paths/line numbers cited below, which reflect the state as of
2026-09-10.)*

- [x] De-duplicate: #440 and #447 closed as duplicates of [#439](https://github.com/roadiz/core-bundle-dev-app/issues/439) and [#446](https://github.com/roadiz/core-bundle-dev-app/issues/446); `roadiz/roadiz#415` is unreachable (issues disabled on that repo), [#462](https://github.com/roadiz/core-bundle-dev-app/issues/462) is the canonical record
- [x] Resync board ↔ `v2.8` milestone (done 2026-10-28; every open v2.8 issue is now on the board)
- [x] Decide on the 7 old, off-topic backend tickets (#16, #393, #399, #406, #428, #457, #470): already removed from the `v2.8` milestone (checked 2026-10-28)
- [x] ~~Set a due date on `v2.8`~~ excluded from Phase 0 (2026-10-28): `v2.8` keeps its whole scope, the team sets the date separately
- [x] Fix `CLAUDE.md`: Rozier runs on Vue 2.7, not Vue 3
- [x] [#533](https://github.com/roadiz/core-bundle-dev-app/issues/533) Remove the dead stack-tree refresh: `refreshNodeTree()`, `fetchNodeTree()` and `treeAvailable()` in [StackNodeTree.js](../app/widgets/StackNodeTree.js) (with their `window.Rozier.initNestables()` call) (the matching branch of `RzAside.refreshAllNodeTrees()` went away with #396). Not a user-facing crash: `treeAvailable()` looks for `.nodetree-widget`, which no template renders since #279 (checked in code and in the browser, 2026-10-28). Keep the quick-add flow, still live
- [x] [#534](https://github.com/roadiz/core-bundle-dev-app/issues/534) Remove the 6 dead Vue files (also settles the `RzButton.vue`/`RzTextarea.vue` collision with their custom elements, and unregisters the containers from `App.js`): `containers/DrawerContainer.vue`, `containers/NodeTypesDrawerContainer.vue`, `containers/TagsEditorContainer.vue`, `components/RzButton.vue`, `RzTextarea.vue`, `CodeMirror.vue`
- [ ] [#535](https://github.com/roadiz/core-bundle-dev-app/issues/535) Remove the dead UIkit behaviours/imports in `main.js`/`vendor.less`: `nestable`, `datepicker`, `pagination`, `notify`, `htmleditor`, + the 12 orphaned vendored CSS files. `switcher` and `sortable` move to Phase 1: JS still references them (`[data-uk-switcher]` in `RzMarkdownEditor.ts`, `JsonEditor.js`, `CssEditor.js`, `YamlEditor.js`; `change.uk.sortable` in `CustomFormFieldsPosition.js`)
- [x] [#536](https://github.com/roadiz/core-bundle-dev-app/issues/536) Clean up the dead fields in `Lazyload.ts` (`inputLengthWatcher`, `documentUploader`, `geotagField`, `multiGeotagField`, `tagAutocomplete`)
- [x] Frame rendering and customisation (2026-10-30): server-rendered first is a standard in [`engineering-practices.md`](engineering-practices.md#rendering), existing JS rendering is fixed zone by zone; projects customise through documented extension points ([ADR 0006](adr/0006-projects-customise-through-public-mechanisms.md), [developer docs](../../../docs/extensions/backoffice_extension_points.md))
- [ ] [#539](https://github.com/roadiz/core-bundle-dev-app/issues/539) Bring Storybook to the [`storybook.md`](storybook.md) convention. **Outside the 2-3 day time-box**, but done before Phase 1 so the migrations and the pilot zone start from a clean Storybook. Three PRs: (1) docs: `storybook.md`, [ADR 0004](adr/0004-shared-options-contract-for-twig-and-stories.md), glossary, pre-PR `build-storybook` check; (2) migrate the 51 story files (tree, naming, args, renderers), fix the broken `rzButton` import in `RzLivePreview.stories.ts`, drop the dead `a11y` block from `.storybook/preview.ts`; (3) add the missing stories for `rz-page`, `rz-bulk-section`, `rz-bulk-actions`, `rz-aside`, `rz-geotag`, `rz-node-tree-contextual-menu`, `rz-save-button`, cover `rz-header-nav-item-button` in `Components/Navigation/HeaderNavItem`, and file `Header` and `Aside` under `Patterns/` (`rz-toast-list` is already covered by `Toast`)
- [ ] [#540](https://github.com/roadiz/core-bundle-dev-app/issues/540) Move the `RoadizRozierBundle` Twig templates to the [`templates.md`](templates.md) tree. Blocked by #539, outside the 2-3 day time-box, before Phase 1. Three PRs: (1) tree: `layouts/`, `patterns/`, `components/`, duplicates merged, the 14 unreferenced templates removed, `UPGRADE.md` updated ([ADR 0005](adr/0005-twig-components-move-in-v2-8.md)); (2) component options aligned on the renderer `Options` types; (3) components for the hand-written `rz-*` roots and their replacement. Legacy trees stay in `widgets/` until the Navigation zone

## Phase 1 — The Vue+Vuex block and the actual jQuery/UIkit removal

Investigation done (see the [appendix](#appendix--component-inventory-2026-09-10)): this is no longer an open question of "should we remove
Vue/UIkit", it's a precise, bounded scope. It runs before the zones are redesigned: components are migrated
**as they are** (same rendering and behaviour, custom elements on Twig-rendered markup per
[`engineering-practices.md`](engineering-practices.md#rendering)), and the zone's redesign comes later.
The dismissible alert is the one exception: it has no native equivalent and needs a minimal design.

- [ ] Replace the Vuex store (6 modules) and migrate the coupled block: `ExplorerContainer.vue`, `FilterExplorerContainer.vue`, `DocumentPreviewContainer.vue` and their dynamic children
- [ ] Migrate the 7 remaining isolated Vue files (`Overlay.vue`, `BlanchetteEditorContainer.vue`/`BlanchetteToolbar.vue`...) following the event-driven pattern already proven on `rz-drawer`
- [ ] Handle the last genuinely live UIkit behaviours: dismissible alert (`data-uk-alert` × 8, no native equivalent yet — needs a minimal design), `switcher` and `sortable` (moved here from Phase 0, [#535](https://github.com/roadiz/core-bundle-dev-app/issues/535)), and finish wiring `RzTooltip.ts` everywhere
- [ ] Clean up the 4 files with a UIkit/native double dependency (`RzEntityThumbnail.ts`, `RzMarkdownEditor.ts`, `RzAside.ts`, `layouts/base.html.twig`)
- [ ] Remove jQuery + UIkit from the bundle (`main.js`, `vendor.less`) — only possible once the two previous points are done, since jQuery and UIkit 2.x are coupled at runtime
- [ ] Write a manual QA checklist per removed behaviour (no automated test exists in `lib/Rozier` to safety-net these removals)
- [ ] Mark [ADR 0001](adr/0001-custom-elements-over-vue-uikit-jquery.md) as fully applied once this scope is handled

## Phase 2 — Technical foundations

- [ ] [#205](https://github.com/roadiz/core-bundle-dev-app/issues/205) Vite: dedicated CSS entry point
- [ ] [#541](https://github.com/roadiz/core-bundle-dev-app/issues/541) Spike: synchronise Twig and Storybook component rendering (endpoint, generated fixtures or Twig in the browser); recommend a mechanism and its timing, or postpone ([ADR 0004](adr/0004-shared-options-contract-for-twig-and-stories.md))
- [x] ~~[#218](https://github.com/roadiz/core-bundle-dev-app/issues/218) `MutationObserver`~~ closed as wontfix
- [ ] [#230](https://github.com/roadiz/core-bundle-dev-app/issues/230) remove global config
- [x] [#248](https://github.com/roadiz/core-bundle-dev-app/issues/248) status icons

## Phase 3 — Pilot zone: content editing page (node-source)

- [ ] Compare the zone's components against the Figma "Structure" and "Markdown" sections
- [ ] Look in Figma for a dedicated dark mode section/variants (none seen in the sample reviewed — confirm before concluding dark mode isn't mocked up)
- [ ] Implement the `MarkdownQuickView` panel per the existing Figma design ([#443](https://github.com/roadiz/core-bundle-dev-app/issues/443))
- [ ] Fix [#444](https://github.com/roadiz/core-bundle-dev-app/issues/444) — lost syntax highlighting
- [ ] Fix [#434](https://github.com/roadiz/core-bundle-dev-app/issues/434) — version disclaimer
- [ ] Fix [#442](https://github.com/roadiz/core-bundle-dev-app/issues/442) — 66/33 grid ratio
- [ ] Fix [#435](https://github.com/roadiz/core-bundle-dev-app/issues/435) — boolean field height
- [ ] Fix [#438](https://github.com/roadiz/core-bundle-dev-app/issues/438) — `false`/`disabled` switch state
- [ ] Validate dark mode on this zone only (it serves as the reference for Phase 4)
- [ ] Fill in the zone's missing Storybook stories
- [ ] Wire up `RzTooltip.ts` (already ready) for the zone's remaining `data-uk-tooltip` usages
- [ ] Mark the zone "done"

## Phase 4 — Dark mode / contrast (cross-cutting, after the pilot zone)

- [ ] Use the validated pilot zone as the contrast reference
- [ ] Fix [#436](https://github.com/roadiz/core-bundle-dev-app/issues/436), [#445](https://github.com/roadiz/core-bundle-dev-app/issues/445), [#446](https://github.com/roadiz/core-bundle-dev-app/issues/446), [#448](https://github.com/roadiz/core-bundle-dev-app/issues/448), [#449](https://github.com/roadiz/core-bundle-dev-app/issues/449) against that reference
- [ ] Go back over the nearly-finished zones (Navigation, Tables) to validate their dark mode

## Phase 5 — Next zones (in the order from the table)

- [ ] Navigation/tree: [#469](https://github.com/roadiz/core-bundle-dev-app/issues/469), [#437](https://github.com/roadiz/core-bundle-dev-app/issues/437), migrate `AdminMenuNav.js`
- [ ] Media library/documents: migrate `DocumentAlignmentWidget.js`
- [ ] Dialogs/overlays: clean up the remaining legacy components (`Overlay`, `FilterExplorerItem`, `JoinPreviewItem`); `WarningModal` and `ModalContainer` are replaced by native `rz-dialog` ([#482](https://github.com/roadiz/core-bundle-dev-app/pull/482))
- [ ] Explorer/Drawer: migrate the remaining legacy files (`NodeTypesDrawerContainer.vue` was dead and removed in #534)

## Phase 6 — Remaining heavy zones

- [ ] Scope Tags/Folders with a real Figma design pass (not just code catch-up)
- [ ] Scope Custom forms (the v2.8 regression [#537](https://github.com/roadiz/core-bundle-dev-app/issues/537), lost drag-and-drop reordering of fields, is fixed: the fields list uses the `rz_table` sortable option and `CustomFormFieldsPosition.js` is removed)
- [ ] Scope User management (no existing dedicated component — the biggest remaining effort)

## Phase 7 — New features

- [ ] [#255](https://github.com/roadiz/core-bundle-dev-app/issues/255) embedded backoffice via iframe
- [ ] [#332](https://github.com/roadiz/core-bundle-dev-app/issues/332) native OIDC migration

## Rules to avoid spreading thin (reminder)

- Only one "active" zone visible at a time (e.g. in the current milestone).
- Don't open a redesign PR on a new zone until the active zone is "done".
- Any idea outside the active zone goes to the `v2.9` backlog, not into the current sprint.

## Appendix — Component inventory (2026-09-10)

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
| Explorer/Drawer (related-entity picker) | 9 | 6 | 3 (incl. `NodeTypesDrawerContainer.vue`, still heavily UIkit despite being touched by the squash commit; removed as dead code in #534) | 2/9 |
| Tags/Folders | 6 | 1 | 5 | 0/6 |
| Login/search | 2 | 1 | 1 | 2/2 |
| Custom forms (fields reordering, via `RzTable`) | 1 | 1 | 0 | 0/1 |
| User management | 0 dedicated component (Symfony/Twig + legacy `assets/less/users/`) | — | — | — |

**Quantified technical debt** (scope of [ADR 0001](adr/0001-custom-elements-over-vue-uikit-jquery.md)):
- **19 files** still use UIkit classes (`uk-*`), including some already "redesigned" files
  (e.g. `custom-elements/RzAside.ts`, `RzEntityThumbnail.ts`, `RzMarkdownEditor.ts`).
- **5 files** still use jQuery directly; jQuery/jQuery UI/UIkit remain loaded globally on every
  page via `main.js`.
- The global `window.Rozier` singleton (targeted by ADR 0001) is still used by ~15 files,
  including already-redesigned components.
- 78 legacy `.less` files (including all vendored UIkit) vs. 54 `assets/css/components/rz-*.css`
  files from the new design system.
- **Notable case**: `components/RzButton.vue` (legacy, `uk-button` markup) is still imported
  alongside the real redesigned `custom-elements/RzButton.ts` — a naming collision to clean up
  (settled in #534: the Vue file was dead and removed).
- **Acknowledged methodological uncertainty**: `custom-elements/RzSelect.ts` has CSS updated by the
  redesign but JS logic unchanged since before the project — visually current, architecturally
  legacy. This kind of case can't be settled by Git analysis alone; a visual check per zone remains
  necessary (see the methodology above).

Storybook documents 24 of the 67 files (~36%, ~49% of the redesigned ones, **0% of the legacy
ones**): a good indicator of coverage per zone, but not a complete functional inventory on its own.

This is a dated snapshot: re-audit before relying on the numbers several weeks later.
