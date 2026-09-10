# État des lieux — refonte graphique de Rozier

> Document de suivi ponctuel, produit le 2026-09-10 à partir du board GitHub
> [`orgs/roadiz/projects/1`](https://github.com/orgs/roadiz/projects/1), de l'historique Git
> de `develop`, d'un inventaire des composants `lib/Rozier`, et d'un échantillon de maquettes Figma.
> À rafraîchir si utilisé plus tard : les statuts d'issues, de PR et de branches évoluent vite.
>
> **Corrigé le 2026-09-10** : la première version listait `feature/sortable-table` et
> `feature/blanchette-editor-and-modal` comme branches non fusionnées. Vérification faite, ces deux
> branches avaient en réalité déjà été mergées début février 2026 (PR [#383](https://github.com/roadiz/core-bundle-dev-app/pull/383) et
> [#389](https://github.com/roadiz/core-bundle-dev-app/pull/389)) puis supprimées — l'erreur venait de refs Git locales obsolètes
> (`origin/*` non purgées après un `git fetch` sans `--prune`). Aucun travail n'a été perdu. Section
> corrigée plus bas avec les vraies 5 branches restantes, qui sont en fait déjà des **PR ouvertes**.

## Résumé exécutif

- **v2.7 est livré et clos** : les 36 tickets du milestone sont à `Done` sur le board.
- Le gros de la refonte visuelle est arrivé en un seul merge (`#279`, 2026-02-02), suivi d'un mois
  de finitions actives jusqu'au **3-4 mars 2026**. **Le chantier est à l'arrêt depuis** — seuls des
  commits de maintenance sans lien avec le design sont passés depuis.
- **5 pull requests ouvertes contiennent du travail non intégré** (entre 1 et 15 commits chacune),
  dont une seule (#396) est prête sans être en brouillon, et une (#398) a des conflits.
- **v2.8** (le backlog post-refonte) n'a été ni priorisé ni commencé : 24 tickets sur le board,
  tous au statut `(no status)` sauf un seul `Todo`.
- Le board et le vrai milestone GitHub `v2.8` ont divergé : **13 issues ouvertes du milestone ne
  sont même pas sur le board**, dont 2 paires de doublons.
- Un inventaire du code (voir plus bas) montre que **49 des 67 fichiers UI de Rozier sont déjà
  redessinés**, mais que Rozier tourne en **Vue 2.7** (pas Vue 3 comme indiqué dans `CLAUDE.md`) —
  point qui pèse directement sur l'ambition de `roadmap.md` de retirer Vue.

## Ce qui a été livré (v2.7)

Le milestone `v2.7` (échéance initiale : 2025-11-30) est terminé à 100% (36/36 tickets `Done`).

Le cœur de la refonte est entré dans `develop` via un unique commit de squash :

> `024c2d9e feat: brand new UI (#279)` — 2026-02-02, 801 fichiers modifiés
> (+33 788 / -18 453 lignes), périmètre `lib/Rozier` + `lib/RoadizRozierBundle`.

Ce commit agrège une longue branche d'intégration `2.7-ui` (supprimée depuis) qui avait elle-même
rassemblé une dizaine de sous-branches (`feat/rz-header`, `feat/rz-tooltip`, `style/rz-form`,
`feature/repeatable-widget`, `feature/rz-login-page`, etc.) construites entre l'automne 2025 et
janvier 2026.

Autres livrables notables du même milestone, visibles sur le board :
- Storybook (mise en place + palette de couleurs, [#203](https://github.com/roadiz/core-bundle-dev-app/issues/203))
- Color scheme & theming ([#232](https://github.com/roadiz/core-bundle-dev-app/issues/232))
- Nouveau composant `rz-tablist` en remplacement de la navbar ([#298](https://github.com/roadiz/core-bundle-dev-app/pull/298))
- Nouveau composant `rz-input` en remplacement des checkboxes ([#300](https://github.com/roadiz/core-bundle-dev-app/pull/300))
- Refonte des formulaires ([#295](https://github.com/roadiz/core-bundle-dev-app/pull/295), [#362](https://github.com/roadiz/core-bundle-dev-app/pull/362))
- Mise en place de Deptrac ([#275](https://github.com/roadiz/core-bundle-dev-app/pull/275))

**Portée confirmée** : strictement `lib/Rozier` (front Vue 3/TS) et `lib/RoadizRozierBundle`
(templates Twig + assets compilés). Aucune trace de travail sur le Nuxt starter public ni sur des
design tokens partagés inter-dépôts — ce n'est pas dans le périmètre de cette refonte.

## Chronologie de l'arrêt

| Date | Événement |
|---|---|
| Automne 2025 – janv. 2026 | Développement sur des sous-branches unitaires (`feat/rz-header`, `feat/rz-tooltip`, `style/rz-form`…), intégrées au fur et à mesure sur `2.7-ui`. |
| 2026-02-02 | Merge squash `024c2d9e feat: brand new UI (#279)` dans `develop`. |
| Février – 3/4 mars 2026 | Finitions actives : styles `RzAside`, `rz-tree`, `rzBulkActions`, `rz-table`, icônes, menu d'actions draggable, drawer, tooltip. |
| Depuis mars 2026 | **Silence sur le design.** Seuls des commits de maintenance sans rapport passent sur `lib/Rozier` : `postcss-pxtorem` (15/04), ajustements sur les documents (27-29/04), correctif Vue mineur sur `DocumentPreviewListItem` (29/05). |

## Travail non récupéré (5 pull requests ouvertes)

Les 5 branches restantes sont en fait déjà proposées en PR sur `develop` — ce n'est pas du travail
à "récupérer" mais des PR à relire et merger. Toutes passent la CI (`SUCCESS`) et sont `MERGEABLE`
sauf une.

| PR | Titre | Créée | Taille | Statut |
|---|---|---|---|---|
| [#416](https://github.com/roadiz/core-bundle-dev-app/pull/416) | fix(ExplorerStoreModule): remove value for filters to correct load more | 2026-03-27 | 1 fichier, ±1 ligne | Prête, mergeable — **quasi zéro risque** |
| [#415](https://github.com/roadiz/core-bundle-dev-app/pull/415) | style: improved style | 2026-03-27 | 4 fichiers, +19/-2 | Draft, mergeable, CI verte |
| [#417](https://github.com/roadiz/core-bundle-dev-app/pull/417) | feat: add functionality to close popover on button click in contextual menu | 2026-03-27 | 2 fichiers, +61/-27 | Draft, mergeable, CI verte |
| [#396](https://github.com/roadiz/core-bundle-dev-app/pull/396) | Fix: refresh RzAside tree depending on page tree action | 2026-02-12 | 24 fichiers, +406/-662 | Prête, mergeable, CI verte, **stale ~7 mois** — à retester avant merge |
| [#398](https://github.com/roadiz/core-bundle-dev-app/pull/398) | feat: add rzActionMenu user drag feature | 2026-02-13 | 3 fichiers, +68/-5 | Draft, **CONFLICTING** — conflits à résoudre. Une PR antérieure (#377) sur le même sujet a été fermée, à vérifier avant de reprendre #398 pour ne pas dupliquer le travail. |

Aucune des 5 n'a de review enregistrée à ce jour (`reviewDecision` vide).

**Recommandation** : merger #416 immédiatement (risque nul), puis #415/#417 après un passage de
revue rapide (petits diffs, CI verte), puis re-tester #396 avant merge (le plus gros diff et le
plus ancien), et traiter #398 en dernier (résolution de conflits + vérifier le lien avec #377).

## Backlog restant (milestone v2.8)

24 tickets sont actuellement sur le board sous `v2.8`, tous non commencés. Regroupés par thème :

**Dark mode / contrastes** (le sujet qui revient le plus souvent — 5 tickets, dont un doublon)
- [#436](https://github.com/roadiz/core-bundle-dev-app/issues/436) — Trop de contraste/bordures visibles en dark mode
- [#445](https://github.com/roadiz/core-bundle-dev-app/issues/445) — Contraste fond de page / contrôles trompeur (semble désactivé)
- [#446](https://github.com/roadiz/core-bundle-dev-app/issues/446) / [#447](https://github.com/roadiz/core-bundle-dev-app/issues/447) *(doublon)* — Couleurs/tailles de police et fond peu lisibles
- [#448](https://github.com/roadiz/core-bundle-dev-app/issues/448) — Layout perçu comme plus encombré et moins lisible
- [#449](https://github.com/roadiz/core-bundle-dev-app/issues/449) — Contraste insuffisant menu d'actions / contenu en dark mode

**Composants de formulaire**
- [#435](https://github.com/roadiz/core-bundle-dev-app/issues/435) — Champs booléens trop hauts, optimiser l'espace vertical
- [#438](https://github.com/roadiz/core-bundle-dev-app/issues/438) — Switch booléen : état `false` identique à `disabled`

**Éditeur markdown**
- [#443](https://github.com/roadiz/core-bundle-dev-app/issues/443) — Preview cassée, besoin d'un vrai mode plein écran avec aperçu temps réel
- [#444](https://github.com/roadiz/core-bundle-dev-app/issues/444) — Coloration syntaxique perdue

**Navigation / organisation**
- [#437](https://github.com/roadiz/core-bundle-dev-app/issues/437) — Loader principal mal positionné depuis le nouveau layout
- [#441](https://github.com/roadiz/core-bundle-dev-app/issues/441) — Le menu d'actions chevauche le panneau de contenu
- [#469](https://github.com/roadiz/core-bundle-dev-app/issues/469) — Garder plusieurs arbres (node/folder/tag) accessibles simultanément

**Fiche de contenu (node source)**
- [#434](https://github.com/roadiz/core-bundle-dev-app/issues/434) — Pas de disclaimer si édition sur une autre version
- [#442](https://github.com/roadiz/core-bundle-dev-app/issues/442) — Ratio de grille titre/date de publication à passer de 50/50 à 66/33

**Fondations techniques**
- [#205](https://github.com/roadiz/core-bundle-dev-app/issues/205) — [Vite] utiliser un entry point CSS dédié
- [#218](https://github.com/roadiz/core-bundle-dev-app/issues/218) — `MutationObserver` pour initialiser les comportements JS au changement de DOM
- [#230](https://github.com/roadiz/core-bundle-dev-app/issues/230) — Retirer la config globale de Rozier
- [#248](https://github.com/roadiz/core-bundle-dev-app/issues/248) — Icônes de statut (intégration)
- [#255](https://github.com/roadiz/core-bundle-dev-app/issues/255) — Backoffice embarqué en iframe via query-param
- [#332](https://github.com/roadiz/core-bundle-dev-app/issues/332) — Migrer le bundle OpenID vers l'authentification OIDC native Symfony

**Dans le même milestone mais hors refonte visuelle, absents du board** (voir section suivante)
- [#128](https://github.com/roadiz/core-bundle-dev-app/issues/128), [#134](https://github.com/roadiz/core-bundle-dev-app/issues/134), [#171](https://github.com/roadiz/core-bundle-dev-app/issues/171), [#409](https://github.com/roadiz/core-bundle-dev-app/issues/409)

## Hygiène board / milestone

Le board (24 items `v2.8`) et le vrai milestone GitHub `v2.8` (31 issues) ont divergé :
**13 issues ouvertes du milestone ne sont pas sur le board**.

- **Doublons à trancher** :
  - [#439](https://github.com/roadiz/core-bundle-dev-app/issues/439) / [#440](https://github.com/roadiz/core-bundle-dev-app/issues/440) — même titre, icône menu custom-form
  - [#446](https://github.com/roadiz/core-bundle-dev-app/issues/446) / [#447](https://github.com/roadiz/core-bundle-dev-app/issues/447) — même titre, contraste UI (déjà listés ci-dessus)
  - [roadiz/roadiz#415](https://github.com/roadiz/roadiz/issues/415) / [core-bundle-dev-app#462](https://github.com/roadiz/core-bundle-dev-app/issues/462) — même bug (zone de drag-and-drop de l'uploader) rapporté dans deux repos différents de l'org ; le board agrège plusieurs repos, `#415` ici n'a aucun rapport avec la PR `#415` de `core-bundle-dev-app` citée plus haut (simple collision de numérotation entre repos). Les deux sont déjà sur le board, sans milestone.
- **Issues liées à la refonte mais jamais triagées sur le board** : #128, #134, #171, #409 (listées ci-dessus).
- **Vieux tickets backend sans lien avec la refonte visuelle**, dans le milestone `v2.8` mais jamais triagés sur le board : [#16](https://github.com/roadiz/core-bundle-dev-app/issues/16), [#393](https://github.com/roadiz/core-bundle-dev-app/issues/393), [#399](https://github.com/roadiz/core-bundle-dev-app/issues/399), [#406](https://github.com/roadiz/core-bundle-dev-app/issues/406), [#428](https://github.com/roadiz/core-bundle-dev-app/issues/428), [#457](https://github.com/roadiz/core-bundle-dev-app/issues/457), [#470](https://github.com/roadiz/core-bundle-dev-app/issues/470) — probablement à sortir du milestone plutôt qu'à traiter dans ce chantier.
- `v2.8` n'a **pas de date d'échéance** (`due_on: null`), contrairement à `v2.7`.

Ce document se limite à recenser ces écarts — aucune action n'a été effectuée sur GitHub à ce stade.

## Inventaire des composants (`lib/Rozier`)

Inventaire réalisé le 2026-09-10 (67 fichiers UI trackés : 21 composants Vue dans `app/components/`
et `app/containers/`, 38 CustomElements dans `app/custom-elements/`, auto-enregistrés via
`import.meta.glob`). Datation croisée entre `git show --name-status 024c2d9e` (liste exacte du
commit de refonte) et l'historique complet par fichier, pour ne pas se fier aux commits de merge
qui faussent `git log -1`.

**Bilan global : 49 fichiers redessinés / 18 legacy** (non touchés depuis avant le 2026-02-02).

| Domaine fonctionnel | Total | Redessinés | Legacy | Story Storybook |
|---|---|---|---|---|
| Tableaux/listings/bulk (`RzTable`, `RzBulkActions`, `RzActionsMenu`) | 4 | 4 | 0 | 2/4 |
| Fiche de contenu / node-source (`RzInput`, `RzFormField`, `RzMarkdownEditor`, `RzRepeatable`...) | ~14 | ~13 | 0 net | 7+/14 |
| Navigation/arbre (`RzAside`, `RzTree`, `RzHeader*`) | 9 | 8 | 1 (`AdminMenuNav.js`) | 4/9 |
| Médiathèque/documents (`RzFileUpload`, `Blanchette*`) | 8 | 7 | 1 (`DocumentAlignmentWidget.js`) | 2/8 |
| Dialogues/overlays transverses (`RzDialog`, `RzPopover`, `RzTooltip`...) | 13 | 7 | 6 | 7/13 |
| Explorateur/Drawer (sélection d'entités liées) | 9 | 6 | 3 (dont `NodeTypesDrawerContainer.vue`, encore massivement UIkit malgré son passage dans le commit squash) | 2/9 |
| Tags/Folders | 6 | 1 | 5 | 0/6 |
| Login/recherche | 2 | 1 | 1 | 2/2 |
| Formulaires personnalisés | 1 | 0 | 1 | 0/1 |
| Gestion utilisateurs | 0 composant dédié (Symfony/Twig + `assets/less/users/` legacy) | — | — | — |

**Dette technique quantifiée** (pertinente pour `roadmap.md`) :
- **19 fichiers** utilisent encore des classes UIkit (`uk-*`), y compris certains fichiers déjà
  "redessinés" (ex. `custom-elements/RzAside.ts`, `RzEntityThumbnail.ts`, `RzMarkdownEditor.ts`).
- **5 fichiers** utilisent encore jQuery directement ; jQuery/jQuery UI/UIkit restent chargés
  globalement sur chaque page via `main.js`.
- Le singleton global `window.Rozier` (visé par `roadmap.md`) est encore utilisé par ~15 fichiers,
  y compris des composants déjà redessinés.
- 78 fichiers `.less` legacy (dont tout UIkit vendorisé) contre 54 fichiers `assets/css/components/rz-*.css`
  du nouveau design system.
- **Cas notable** : `components/RzButton.vue` (legacy, markup `uk-button`) est encore importé en
  parallèle du vrai `custom-elements/RzButton.ts` redessiné — collision de nom à nettoyer.
- **Doute méthodologique assumé** : `custom-elements/RzSelect.ts` a un CSS mis à jour par la refonte
  mais une logique JS inchangée depuis avant le projet — visuellement à jour, architecturalement
  legacy. Ce genre de cas ne peut pas être tranché par la seule analyse Git ; une vérification
  visuelle par zone reste nécessaire (voir TODO).

Storybook documente 24 des 67 fichiers (~36%, ~49% des redessinés, **0% des legacy**) : c'est un
bon indicateur de couverture par zone, mais pas un inventaire fonctionnel complet à lui seul.

Détail complet dans l'historique de conversation ayant produit ce document ; à ré-auditer si ce
fichier est réutilisé plusieurs semaines plus tard.

## Confrontation aux maquettes Figma (échantillon)

Comparaison faite sur 3 sections du fichier Figma [`Roadiz - V3.0`](https://www.figma.com/design/RS9Difo5w26fBkQRLG7UAt/Roadiz---V3.0)
fournies par l'utilisateur (échantillon, pas une couverture exhaustive du fichier) :

- **Section "Markdown"** : la maquette contient déjà un panneau **"MarkdownQuickView"** (aperçu
  affiché à côté du champ d'édition, pas une modale plein écran). Ça répond directement à
  [#443](https://github.com/roadiz/core-bundle-dev-app/issues/443) ("besoin d'un vrai mode plein écran avec aperçu") : ce n'est pas une question de
  design à trancher, c'est un écart d'implémentation — le design existe déjà.
- **Section "Structure"** (fiche de contenu) : contient un composant **"Edit / FloatingBar"** déjà
  spécifié dans plusieurs états (liens, repeatable, carte). Il correspond à la fonctionnalité de la
  PR [#398](https://github.com/roadiz/core-bundle-dev-app/pull/398) (`feature/rz-action-menu-dragging`, actuellement en conflit) — là aussi le design
  est prêt, il ne reste que l'implémentation à finir.
- **Section "Dashboard"** : uniquement en thème clair dans cet échantillon.
- **Aucune variante dark mode n'apparaît dans les 3 sections examinées.** Point non tranché,
  volontairement — à vérifier directement dans Figma (chercher une section ou des variantes dark
  mode dédiées) avant de conclure que le dark mode n'est pas maquetté. Ne pas deviner.

Le fichier Figma n'a qu'une seule page top-level ("Cover") mais organise le contenu en grandes
"sections" positionnées sur un même canvas (Dashboard, Markdown, Structure...) — un inventaire
exhaustif des sections nécessiterait de parcourir le fichier directement dans Figma plutôt que de
deviner des node-id.

## Dette technique legacy : UIkit, Vue, JS global — état des lieux détaillé

`lib/Rozier/docs/roadmap.md` vise à terme à retirer `Rozier.js`, `Lazyload.js`, jQuery, UIkit et
**Vue**, au profit de `CustomElements` natifs. Investigation faite le 2026-09-10 (3 explorations
indépendantes) pour savoir si c'est pertinent d'attaquer ça dès la reprise. **Constat principal :
la refonte a déjà fait la majorité du travail d'extraction sans que `roadmap.md` ait été mis à
jour** — ce ne sont plus 3 gros chantiers à démarrer, mais surtout du nettoyage de code déjà mort,
plus un nombre réduit de blocs réellement couplés.

### UIkit (2.27.4 — branche 2.x, dépendante de jQuery)

- Sur les ~19-22 fichiers avec des classes `uk-*`, la grande majorité est **du CSS/naming sans
  runtime JS**. Aucun appel `UIkit.modal()`/`UIkit.dropdown()` en dur trouvé nulle part.
- **Comportements JS UIkit réellement morts** (importés dans `main.js`/`vendor.less` mais sans
  markup ni handler qui les déclenche encore) : `switcher` (4 fichiers le référencent en vain :
  `RzMarkdownEditor.ts`, `YamlEditor.js`, `JsonEditor.js`, `CssEditor.js`), `sortable` (le gabarit
  cible a été reconstruit sans drag-and-drop), `nestable`, `datepicker`, `pagination`, `notify`,
  `htmleditor` — supprimables sans réimplémentation. Plus **12 fichiers CSS vendorisés orphelins**
  (jamais importés).
- **Comportements JS réellement vivants à traiter : seulement 2** — l'alert dismissible
  (`data-uk-alert` × 8 gabarits, pas d'équivalent natif dédié) et le tooltip legacy
  (`data-uk-tooltip` × 8, mais `RzTooltip.ts` existe déjà et attend juste d'être branché dessus).
- Équivalents natifs déjà en place et adoptés : `RzDialog` (remplace `uk-modal`), `RzPopover`
  (remplace `uk-dropdown`, déjà utilisé dans 10 gabarits), `RzTablist`, `RzDrawer`, `RzToastList`.
- 4 fichiers ont une double dépendance (natif + reliquat UIkit) à assainir :
  `RzEntityThumbnail.ts`, `RzMarkdownEditor.ts`, `RzAside.ts`, `base.html.twig`.

### Vue (2.7.16, EOL depuis fin 2023 — et non Vue 3 comme l'indique `CLAUDE.md`, à corriger)

21 fichiers `.vue`. Montage via 3 mécanismes dans `App.js`/`main.js`. State management : Vuex 3.0.1,
6 modules, **100% confiné au monde Vue** (aucun custom element n'importe le store).

- **6 fichiers sont déjà morts** (non montés, non importés par un chemin vivant) : `DrawerContainer.vue`
  (remplacé par `<rz-drawer>` dans le même commit de refonte), `NodeTypesDrawerContainer.vue`,
  `TagsEditorContainer.vue`, `RzButton.vue` (collision de nom avec le `RzButton.ts` natif),
  `RzTextarea.vue`, `CodeMirror.vue`. Supprimables directement.
- **7 fichiers sont isolés et suivent déjà le pattern événementiel** prouvé sur `rz-drawer`
  (`CustomEvent` sur `window`/`document`, aucune imbrication Vue/custom-element) : `Overlay.vue`,
  `BlanchetteEditorContainer.vue` + `BlanchetteToolbar.vue` et leurs feuilles. Migrables fichier par
  fichier, sans gros risque.
- **8 fichiers forment un bloc réellement couplé** par Vuex et un montage commun : `ExplorerContainer.vue`,
  `FilterExplorerContainer.vue`, `DocumentPreviewContainer.vue`, `ModalContainer.vue` et leurs
  enfants dynamiques. C'est le seul vrai "gros chantier" restant sur Vue — nécessite de remplacer le
  store Vuex, pas juste de porter des templates.
- **Aucun test** (pas de vitest/jest, Storybook structurellement incapable de rendre du Vue 2).

### JS global / `window.Rozier`

- `Rozier.js` (1095 lignes) a déjà été réécrit en `Rozier.ts` (141 lignes) pendant la refonte : la
  quasi-totalité de la logique d'arbres a été extraite vers `RzAside.ts`/`RzTree.ts`. Il ne reste
  que ~10 fichiers consommateurs de `window.Rozier`, chacun avec un appel isolé et peu profond
  (lecture de messages, délégation vers `<rz-aside>`) — pas de couplage structurel restant.
- **Bug trouvé en vérifiant ce constat** : [`StackNodeTree.js:194`](../app/widgets/StackNodeTree.js#L194)
  appelle `window.Rozier.initNestables()`, méthode qui n'existe plus sur la classe `Rozier` actuelle
  (confirmé en lisant `Rozier.ts` en entier) → `TypeError` à chaque réorganisation d'arbre par
  drag-and-drop, qui empêche aussi l'exécution des lignes suivantes (`bindMainTrees()`,
  `lazyload.bindAjaxLink()`, `resize()`). **À corriger indépendamment de tout chantier de retrait**,
  probablement un bug utilisateur actif. `Lazyload.ts` contient aussi des champs jamais réassignés
  (code mort à nettoyer : `inputLengthWatcher`, `documentUploader`, `geotagField`, `multiGeotagField`, `tagAutocomplete`).
- **jQuery est couplé à UIkit, pas juste aux 5 fichiers qui l'utilisent directement** : UIkit 2.27.4
  exige `window.jQuery` au runtime pour ses composants importés (confirmé par un commentaire
  explicite dans le code : *"HERE WE NEED JQUERY BECAUSE UI-KIT V2 REQUIRE JQUERY"*), invisible au
  graphe de dépendances npm. **On ne peut pas retirer jQuery sans retirer UIkit d'abord** (ou les
  deux ensemble).
- `main.js` charge jQuery/UIkit/jQuery UI/Rozier de façon bloquante sur **toutes** les pages
  authentifiées, utilisées ou non par la page en cours.

### Verdict : pertinent dès la reprise, ou à différer ?

Pas un choix binaire par sujet — il y a 3 niveaux d'effort mélangés dans ces 3 sujets :

1. **Nettoyage quasi gratuit, à faire immédiatement (Phase 0)** : supprimer les 6 fichiers Vue
   morts, les comportements/imports UIkit morts (switcher, sortable, nestable, datepicker,
   pagination, notify, htmleditor) et les 12 CSS vendorisés orphelins, corriger le bug
   `initNestables()`, nettoyer les champs morts de `Lazyload.ts`. Risque très faible, gain immédiat
   en lisibilité, et ça réduit la surface avant de commencer les zones visuelles.
2. **Petites tâches contenues, à glisser dans les zones concernées** : brancher `RzTooltip.ts` sur
   les 8 usages `data-uk-tooltip` restants, traiter les 4 fichiers à double dépendance, migrer les 7
   fichiers Vue isolés au fil de l'eau quand on touche leur zone (ex. `BlanchetteEditorContainer`
   quand on fait la médiathèque).
3. **Le seul vrai "gros chantier" restant** : le bloc Vue+Vuex de 8 fichiers (Explorer/FilterExplorer/
   DocumentPreview/Modal) + le retrait effectif de jQuery/UIkit du bundle (`main.js`, `vendor.less`,
   l'alerte dismissible sans équivalent natif). **Ce n'est pas urgent pour reprendre la refonte
   visuelle**, mais ça mérite d'être planifié comme une phase dédiée après avoir stabilisé 1-2 zones
   visuelles (pour retrouver du rythme d'équipe et un peu de filet avant d'attaquer le plus risqué).
   Sans aucun test automatisé nulle part dans `lib/Rozier`, prévoir une checklist de QA manuelle par
   comportement retiré plutôt que de compter sur une suite de tests inexistante.

En clair : ne pas traiter "retirer UIkit / Vue / JS global" comme 3 fronts séparés à arbitrer contre
la refonte visuelle. La partie 1 est à faire tout de suite (quasi gratuite), la partie 2 se fait au
fil des zones déjà prévues, et seule la partie 3 est un vrai arbitrage de planning — voir
[`ui-redesign-todo.md`](ui-redesign-todo.md) pour son emplacement dans les phases.

## Feuille de route proposée pour la reprise

Voir [`ui-redesign-todo.md`](ui-redesign-todo.md) — méthodologie de reprise et TODO phasée et
actionnable, construite à partir de cet état des lieux, de l'inventaire des composants, et de
l'échantillon Figma ci-dessus.
