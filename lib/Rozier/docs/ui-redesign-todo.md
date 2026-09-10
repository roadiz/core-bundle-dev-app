# TODO — Methodical resumption of the Rozier redesign

> Built from [`ui-redesign-status.md`](ui-redesign-status.md) (status report, component inventory,
> Figma sample). Goal: give a precise framework so we don't work on several big fronts at once —
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

Based on the component inventory (see status report): the nearly-finished zones close quickly and
build momentum; the heavily legacy zones need a real effort, not just touch-ups.

| Order | Zone | Progress | Why this order |
|---|---|---|---|
| 1 (pilot) | Content editing page / node-source | 13/14 | Concentrates most of the open bug tickets; the Figma design for the gaps (MarkdownQuickView, FloatingBar) is already ready; the most-used zone day to day |
| 2 | Navigation / tree | 8/9 | Nearly done, 2 tickets to close out |
| 3 | Media library / documents | 7/8 | Nearly done |
| — | Tables / listings / bulk actions | 4/4 | Already done — just re-validate dark mode when it's tackled in phase 2 |
| 4 | Cross-cutting dialogs / overlays | 7/13 | Used by every other zone — the legacy duplicates (`RzButton.vue`/`RzTextarea.vue`) also pollute the "finished" zones |
| 5 | Explorer / Drawer | 6/9 | `NodeTypesDrawerContainer.vue` remains heavily UIkit despite being touched by the redesign commit |
| 6 | Tags / Folders | 1/6 | The biggest gap — probably not just catch-up work, needs a real design pass before coding |
| 7 | Custom forms | 0/1 | Small footprint but never started |
| 8 | User management | 0 dedicated component | No existing foundation, the biggest remaining effort — to be scoped separately with a dedicated Figma mockup |

## Phase 0 — Hygiene (before writing any code)

This phase is near-free (few design decisions) and makes sense right after the roadmap is
validated: it clears the ground and rebuilds some rhythm before the pilot zone. **Time-box it to
2-3 days** — several items below need a quick manual check before removal (no automated tests in
`lib/Rozier`); don't let it drift into an exhaustive verification effort that delays Phase 1.

**The order below matters**: merge the PRs first, *before* touching the legacy cleanup/bugfix work —
PR [#396](https://github.com/roadiz/core-bundle-dev-app/pull/396) directly modifies `Rozier.ts`, `RzAside.ts`, and `RzTree.ts`,
exactly the files where the `initNestables()` bug and the Vue/UIkit cleanup below live. Fixing/
cleaning up before merging #396 risks a conflict or an overwritten fix.

- [ ] Merge [#416](https://github.com/roadiz/core-bundle-dev-app/pull/416) (near-zero risk)
- [ ] Review + merge [#415](https://github.com/roadiz/core-bundle-dev-app/pull/415) and [#417](https://github.com/roadiz/core-bundle-dev-app/pull/417)
- [ ] Retest (stale ~7 months) then merge [#396](https://github.com/roadiz/core-bundle-dev-app/pull/396)
- [ ] Check the link with the closed PR #377, resolve conflicts, then merge [#398](https://github.com/roadiz/core-bundle-dev-app/pull/398)

*(From here on, the code in `Rozier.ts`/`RzAside.ts`/`RzTree.ts` has moved — work from the
post-merge state, not from the paths/line numbers cited below, which reflect the state as of
2026-09-10.)*

- [ ] De-duplicate: [#439](https://github.com/roadiz/core-bundle-dev-app/issues/439)/[#440](https://github.com/roadiz/core-bundle-dev-app/issues/440), [#446](https://github.com/roadiz/core-bundle-dev-app/issues/446)/[#447](https://github.com/roadiz/core-bundle-dev-app/issues/447), [roadiz/roadiz#415](https://github.com/roadiz/roadiz/issues/415)/[core-bundle-dev-app#462](https://github.com/roadiz/core-bundle-dev-app/issues/462)
- [ ] Resync board ↔ `v2.8` milestone (13 milestone issues missing from the board)
- [ ] Decide on the 7 old, off-topic backend tickets (#16, #393, #399, #406, #428, #457, #470): remove from the milestone or explicitly own them
- [ ] Set a due date on `v2.8`, or open `v2.9` if scope has drifted too far
- [ ] Clean up the `components/RzButton.vue` (legacy) vs `custom-elements/RzButton.ts` (new) collision — and its cousin `RzTextarea.vue`
- [ ] Check whether `components/CodeMirror.vue` is dead code (no import found)
- [ ] Fix `CLAUDE.md`: Rozier runs on Vue 2.7, not Vue 3
- [ ] Look in Figma for a dedicated dark mode section/variants (none seen in the sample reviewed — confirm before concluding dark mode isn't mocked up)
- [ ] **Fix the `window.Rozier.initNestables()` bug** ([StackNodeTree.js:194](../app/widgets/StackNodeTree.js#L194)) — method no longer exists on `Rozier.ts`, crashes on every drag-and-drop tree reorder and blocks the rebind that follows. Independent of any removal effort, likely an active user-facing bug
- [ ] Remove the 6 dead Vue files: `DrawerContainer.vue`, `NodeTypesDrawerContainer.vue`, `TagsEditorContainer.vue`, `RzButton.vue`, `RzTextarea.vue`, `CodeMirror.vue`
- [ ] Remove the dead UIkit behaviours/imports in `main.js`/`vendor.less`: `switcher`, `sortable`, `nestable`, `datepicker`, `pagination`, `notify`, `htmleditor`, + the 12 orphaned vendored CSS files
- [ ] Clean up the dead fields in `Lazyload.ts` (`inputLengthWatcher`, `documentUploader`, `geotagField`, `multiGeotagField`, `tagAutocomplete`)

## Phase 1 — Pilot zone: content editing page (node-source)

- [ ] Compare the zone's components against the Figma "Structure" and "Markdown" sections
- [ ] Implement the `MarkdownQuickView` panel per the existing Figma design ([#443](https://github.com/roadiz/core-bundle-dev-app/issues/443))
- [ ] Fix [#444](https://github.com/roadiz/core-bundle-dev-app/issues/444) — lost syntax highlighting
- [ ] Fix [#434](https://github.com/roadiz/core-bundle-dev-app/issues/434) — version disclaimer
- [ ] Fix [#442](https://github.com/roadiz/core-bundle-dev-app/issues/442) — 66/33 grid ratio
- [ ] Fix [#435](https://github.com/roadiz/core-bundle-dev-app/issues/435) — boolean field height
- [ ] Fix [#438](https://github.com/roadiz/core-bundle-dev-app/issues/438) — `false`/`disabled` switch state
- [ ] Validate dark mode on this zone only (it serves as the reference for phase 2)
- [ ] Fill in the zone's missing Storybook stories
- [ ] Wire up `RzTooltip.ts` (already ready) for the zone's remaining `data-uk-tooltip` usages
- [ ] Mark the zone "done"

## Phase 2 — Dark mode / contrast (cross-cutting, after the pilot zone)

- [ ] Use the validated pilot zone as the contrast reference
- [ ] Fix [#436](https://github.com/roadiz/core-bundle-dev-app/issues/436), [#445](https://github.com/roadiz/core-bundle-dev-app/issues/445), [#446](https://github.com/roadiz/core-bundle-dev-app/issues/446)/[#447](https://github.com/roadiz/core-bundle-dev-app/issues/447), [#448](https://github.com/roadiz/core-bundle-dev-app/issues/448), [#449](https://github.com/roadiz/core-bundle-dev-app/issues/449) against that reference
- [ ] Go back over the nearly-finished zones (Navigation, Tables) to validate their dark mode

## Phase 3 — Next zones (in the order from the table)

- [ ] Navigation/tree: [#469](https://github.com/roadiz/core-bundle-dev-app/issues/469), [#437](https://github.com/roadiz/core-bundle-dev-app/issues/437), migrate `AdminMenuNav.js`
- [ ] Media library/documents: migrate `DocumentAlignmentWidget.js`
- [ ] Dialogs/overlays: clean up the remaining legacy components (`WarningModal`, `ModalContainer`, `Overlay`, `FilterExplorerItem`, `JoinPreviewItem`)
- [ ] Explorer/Drawer: revisit `NodeTypesDrawerContainer.vue` (still heavily UIkit)

## Phase 4 — Technical foundations

- [ ] [#205](https://github.com/roadiz/core-bundle-dev-app/issues/205) Vite: dedicated CSS entry point
- [ ] [#218](https://github.com/roadiz/core-bundle-dev-app/issues/218) `MutationObserver`
- [ ] [#230](https://github.com/roadiz/core-bundle-dev-app/issues/230) remove global config
- [ ] [#248](https://github.com/roadiz/core-bundle-dev-app/issues/248) status icons
- [ ] [#255](https://github.com/roadiz/core-bundle-dev-app/issues/255) embedded backoffice via iframe
- [ ] [#332](https://github.com/roadiz/core-bundle-dev-app/issues/332) native OIDC migration

## Phase 5 — The Vue+Vuex block and the actual jQuery/UIkit removal

Investigation done (see status report): this is no longer an open question of "should we remove
Vue/UIkit", it's a precise, bounded scope. Treat it as a dedicated phase, after 1-2 visual zones
have been stabilized (not urgent, but not to be forgotten):

- [ ] Replace the Vuex store (6 modules) and migrate the coupled 8-file block: `ExplorerContainer.vue`, `FilterExplorerContainer.vue`, `DocumentPreviewContainer.vue`, `ModalContainer.vue` and their dynamic children
- [ ] Migrate the 7 remaining isolated Vue files (`Overlay.vue`, `BlanchetteEditorContainer.vue`/`BlanchetteToolbar.vue`...) following the event-driven pattern already proven on `rz-drawer` — doable along the way, zone by zone, no need to wait for this phase
- [ ] Handle the last 2 genuinely live UIkit behaviours: dismissible alert (`data-uk-alert` × 8, no native equivalent yet — needs designing) and finish wiring `RzTooltip.ts` everywhere
- [ ] Clean up the 4 files with a UIkit/native double dependency (`RzEntityThumbnail.ts`, `RzMarkdownEditor.ts`, `RzAside.ts`, `base.html.twig`)
- [ ] Remove jQuery + UIkit from the bundle (`main.js`, `vendor.less`) — only possible once the two previous points are done, since jQuery and UIkit 2.x are coupled at runtime
- [ ] Write a manual QA checklist per removed behaviour (no automated test exists in `lib/Rozier` to safety-net these removals)
- [ ] Update `roadmap.md` once this scope is handled (it still documents this effort as not started, even though it's already largely underway)

## Phase 6 — Remaining heavy zones

- [ ] Scope Tags/Folders with a real Figma design pass (not just code catch-up)
- [ ] Scope Custom forms
- [ ] Scope User management (no existing dedicated component — the biggest remaining effort)

## Rules to avoid spreading thin (reminder)

- Only one "active" zone visible at a time (e.g. in the current milestone).
- Don't open a redesign PR on a new zone until the active zone is "done".
- Any idea outside the active zone goes to the `v2.9` backlog, not into the current sprint.
