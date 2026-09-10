# TODO — Reprise méthodique de la refonte Rozier

> Construit à partir de [`ui-redesign-status.md`](ui-redesign-status.md) (état des lieux, inventaire
> composants, échantillon Figma). Objectif : donner un cadre précis pour ne pas travailler sur
> plusieurs gros chantiers à la fois — Rozier est une interface large, s'éparpiller dessus produit
> beaucoup de zones à 60% plutôt que des zones finies.

## Méthodologie

**Principe : tranche verticale par zone fonctionnelle, pas horizontale par type de composant.**
Finir une zone à 100% (design conforme, dark mode validé, story Storybook, tickets fermés) avant
d'ouvrir la suivante — plutôt que d'avancer un peu partout en même temps.

Règles pour ne pas s'éparpiller :
1. **Une seule zone "active" à la fois.** Toute autre idée qui surgit va dans le backlog (v2.8/v2.9), pas dans le sprint en cours.
2. **Définition de "done" par zone** : composants legacy migrés ou explicitement exclus, dark mode vérifié sur cette zone, confrontation à la section Figma correspondante faite, tickets backlog liés fermés, story Storybook à jour.
3. **Le dark mode/contraste est transverse** mais ne se propage qu'*après* avoir été validé sur une zone pilote — pas de correctif global non vérifié (c'est le symptôme qu'on voit déjà dans le backlog actuel).
4. **Revue Figma avant chaque zone** : chercher la section correspondante dans le fichier `Roadiz - V3.0` et comparer avant de coder, pas après.
5. **Ne pas deviner ce qui n'est pas vérifiable** (node-id Figma, statut d'une branche, contenu d'un fichier non lu) — vérifier plutôt que d'assumer.

## Ordre des zones et pourquoi

Basé sur l'inventaire composants (voir état des lieux) : les zones presque finies se ferment vite
et créent de la traction ; les zones très legacy demandent un vrai chantier, pas des retouches.

| Ordre | Zone | Avancement | Pourquoi cet ordre |
|---|---|---|---|
| 1 (pilote) | Fiche de contenu / node-source | 13/14 | Concentre la majorité des tickets bugs ouverts ; le design Figma des manques (MarkdownQuickView, FloatingBar) est déjà prêt ; zone la plus utilisée au quotidien |
| 2 | Navigation / arbre | 8/9 | Quasi fini, 2 tickets à boucler |
| 3 | Médiathèque / documents | 7/8 | Quasi fini |
| — | Tableaux / listings / bulk | 4/4 | Déjà fini — juste revalider le dark mode quand on l'attaquera en phase 2 |
| 4 | Dialogues / overlays transverses | 7/13 | Utilisé par toutes les autres zones — les doublons legacy (`RzButton.vue`/`RzTextarea.vue`) polluent aussi les zones "finies" |
| 5 | Explorateur / Drawer | 6/9 | `NodeTypesDrawerContainer.vue` reste massivement UIkit malgré son passage dans le commit de refonte |
| 6 | Tags / Folders | 1/6 | Le plus gros retard — probablement pas juste du rattrapage, nécessite un vrai passage de design avant de coder |
| 7 | Formulaires personnalisés | 0/1 | Petit périmètre mais jamais commencé |
| 8 | Gestion utilisateurs | 0 composant dédié | Aucune base existante, plus gros chantier — à cadrer séparément avec une maquette Figma dédiée |

## Phase 0 — Hygiène (avant de coder quoi que ce soit)

Cette phase est quasi gratuite (peu de décisions de design) et logique juste après validation de la
roadmap : elle déblaie le terrain et redonne du rythime avant la zone pilote. **Time-boxer à 2-3
jours** — plusieurs items ci-dessous demandent une vérification manuelle rapide avant suppression
(pas de tests automatisés dans `lib/Rozier`) ; ne pas la laisser dériver en chantier de vérification
exhaustif qui retarderait la Phase 1.

**L'ordre ci-dessous est important** : merger les PR en premier, *avant* de toucher au nettoyage
legacy/bugfix — la PR [#396](https://github.com/roadiz/core-bundle-dev-app/pull/396) modifie
directement `Rozier.ts`, `RzAside.ts` et `RzTree.ts`, exactement les fichiers où vivent le bug
`initNestables()` et le nettoyage Vue/UIkit ci-dessous. Corriger/nettoyer avant de merger #396
risque un conflit ou un correctif écrasé.

- [ ] Merger [#416](https://github.com/roadiz/core-bundle-dev-app/pull/416) (quasi zéro risque)
- [ ] Revue + merge [#415](https://github.com/roadiz/core-bundle-dev-app/pull/415) et [#417](https://github.com/roadiz/core-bundle-dev-app/pull/417)
- [ ] Re-tester (stale ~7 mois) puis merger [#396](https://github.com/roadiz/core-bundle-dev-app/pull/396)
- [ ] Vérifier le lien avec la PR fermée #377, résoudre les conflits, puis merger [#398](https://github.com/roadiz/core-bundle-dev-app/pull/398)

*(À partir d'ici, le code de `Rozier.ts`/`RzAside.ts`/`RzTree.ts` a bougé — se baser sur l'état
post-merge, pas sur les chemins/lignes cités plus bas qui reflètent l'état du 2026-09-10.)*

- [ ] Dédoublonner : [#439](https://github.com/roadiz/core-bundle-dev-app/issues/439)/[#440](https://github.com/roadiz/core-bundle-dev-app/issues/440), [#446](https://github.com/roadiz/core-bundle-dev-app/issues/446)/[#447](https://github.com/roadiz/core-bundle-dev-app/issues/447), [roadiz/roadiz#415](https://github.com/roadiz/roadiz/issues/415)/[core-bundle-dev-app#462](https://github.com/roadiz/core-bundle-dev-app/issues/462)
- [ ] Resynchroniser board ↔ milestone `v2.8` (13 issues du milestone absentes du board)
- [ ] Statuer sur les 7 vieux tickets backend hors-sujet (#16, #393, #399, #406, #428, #457, #470) : sortir du milestone ou assumer explicitement
- [ ] Fixer une échéance à `v2.8`, ou ouvrir `v2.9` si le périmètre a trop dérivé
- [ ] Nettoyer la collision `components/RzButton.vue` (legacy) vs `custom-elements/RzButton.ts` (nouveau) — et son cousin `RzTextarea.vue`
- [ ] Vérifier si `components/CodeMirror.vue` est du code mort (aucun import trouvé)
- [ ] Corriger `CLAUDE.md` : Rozier tourne en Vue 2.7, pas Vue 3
- [ ] Chercher dans Figma une section/des variantes dark mode dédiées (aucune vue dans l'échantillon consulté — à confirmer avant de considérer que le dark mode n'est pas maquetté)
- [ ] **Corriger le bug `window.Rozier.initNestables()`** ([StackNodeTree.js:194](../app/widgets/StackNodeTree.js#L194)) — méthode inexistante sur `Rozier.ts`, plante à chaque réorganisation d'arbre par drag-and-drop et bloque le rebind qui suit. Indépendant de tout chantier de retrait, probablement un bug utilisateur actif
- [ ] Supprimer les 6 fichiers Vue morts : `DrawerContainer.vue`, `NodeTypesDrawerContainer.vue`, `TagsEditorContainer.vue`, `RzButton.vue`, `RzTextarea.vue`, `CodeMirror.vue`
- [ ] Supprimer les comportements/imports UIkit morts dans `main.js`/`vendor.less` : `switcher`, `sortable`, `nestable`, `datepicker`, `pagination`, `notify`, `htmleditor`, + les 12 CSS vendorisés orphelins
- [ ] Nettoyer les champs morts de `Lazyload.ts` (`inputLengthWatcher`, `documentUploader`, `geotagField`, `multiGeotagField`, `tagAutocomplete`)

## Phase 1 — Zone pilote : Fiche de contenu (node-source)

- [ ] Comparer les composants de la zone aux sections Figma "Structure" et "Markdown"
- [ ] Implémenter le panneau `MarkdownQuickView` selon le design Figma existant ([#443](https://github.com/roadiz/core-bundle-dev-app/issues/443))
- [ ] Fix [#444](https://github.com/roadiz/core-bundle-dev-app/issues/444) — coloration syntaxique perdue
- [ ] Fix [#434](https://github.com/roadiz/core-bundle-dev-app/issues/434) — disclaimer version
- [ ] Fix [#442](https://github.com/roadiz/core-bundle-dev-app/issues/442) — ratio grille 66/33
- [ ] Fix [#435](https://github.com/roadiz/core-bundle-dev-app/issues/435) — hauteur champs booléens
- [ ] Fix [#438](https://github.com/roadiz/core-bundle-dev-app/issues/438) — switch `false`/`disabled`
- [ ] Valider le dark mode sur cette zone uniquement (elle sert de référence pour la phase 2)
- [ ] Compléter les stories Storybook manquantes de la zone
- [ ] Brancher `RzTooltip.ts` (déjà prêt) sur les usages `data-uk-tooltip` restants de la zone
- [ ] Marquer la zone "done"

## Phase 2 — Dark mode / contraste (transverse, après la zone pilote)

- [ ] Utiliser la zone pilote validée comme référence de contraste
- [ ] Fix [#436](https://github.com/roadiz/core-bundle-dev-app/issues/436), [#445](https://github.com/roadiz/core-bundle-dev-app/issues/445), [#446](https://github.com/roadiz/core-bundle-dev-app/issues/446)/[#447](https://github.com/roadiz/core-bundle-dev-app/issues/447), [#448](https://github.com/roadiz/core-bundle-dev-app/issues/448), [#449](https://github.com/roadiz/core-bundle-dev-app/issues/449) avec cette référence
- [ ] Repasser sur les zones déjà quasi finies (Navigation, Tableaux) pour valider leur dark mode

## Phase 3 — Zones suivantes (dans l'ordre du tableau)

- [ ] Navigation/arbre : [#469](https://github.com/roadiz/core-bundle-dev-app/issues/469), [#437](https://github.com/roadiz/core-bundle-dev-app/issues/437), migrer `AdminMenuNav.js`
- [ ] Médiathèque/documents : migrer `DocumentAlignmentWidget.js`
- [ ] Dialogues/overlays : nettoyer les composants legacy restants (`WarningModal`, `ModalContainer`, `Overlay`, `FilterExplorerItem`, `JoinPreviewItem`)
- [ ] Explorateur/Drawer : reprendre `NodeTypesDrawerContainer.vue` (encore massivement UIkit)

## Phase 4 — Fondations techniques

- [ ] [#205](https://github.com/roadiz/core-bundle-dev-app/issues/205) Vite : entry point CSS dédié
- [ ] [#218](https://github.com/roadiz/core-bundle-dev-app/issues/218) `MutationObserver`
- [ ] [#230](https://github.com/roadiz/core-bundle-dev-app/issues/230) retirer la config globale
- [ ] [#248](https://github.com/roadiz/core-bundle-dev-app/issues/248) icônes de statut
- [ ] [#255](https://github.com/roadiz/core-bundle-dev-app/issues/255) backoffice embarqué en iframe
- [ ] [#332](https://github.com/roadiz/core-bundle-dev-app/issues/332) migration OIDC native

## Phase 5 — Le bloc Vue+Vuex et le retrait effectif jQuery/UIkit

Investigation faite (voir état des lieux) : ce n'est plus une question ouverte "faut-il retirer
Vue/UIkit", c'est un périmètre précis et borné. À traiter comme une phase dédiée, après avoir
stabilisé 1-2 zones visuelles (pas en urgence, mais à ne pas oublier) :

- [ ] Remplacer le store Vuex (6 modules) et migrer le bloc couplé de 8 fichiers : `ExplorerContainer.vue`, `FilterExplorerContainer.vue`, `DocumentPreviewContainer.vue`, `ModalContainer.vue` et leurs enfants dynamiques
- [ ] Migrer les 7 fichiers Vue isolés restants (`Overlay.vue`, `BlanchetteEditorContainer.vue`/`BlanchetteToolbar.vue`...) en suivant le pattern événementiel déjà prouvé sur `rz-drawer` — faisable au fil de l'eau, zone par zone, pas besoin d'attendre cette phase
- [ ] Traiter les 2 derniers comportements UIkit réellement vivants : alerte dismissible (`data-uk-alert` × 8, pas d'équivalent natif — à concevoir) et finir le passage à `RzTooltip.ts` partout
- [ ] Assainir les 4 fichiers à double dépendance UIkit/natif (`RzEntityThumbnail.ts`, `RzMarkdownEditor.ts`, `RzAside.ts`, `base.html.twig`)
- [ ] Retirer jQuery + UIkit du bundle (`main.js`, `vendor.less`) — seulement possible une fois les deux points précédents faits, jQuery et UIkit 2.x étant couplés au runtime
- [ ] Écrire une checklist de QA manuelle par comportement retiré (aucun test automatisé n'existe dans `lib/Rozier` pour sécuriser ces retraits)
- [ ] Mettre à jour `roadmap.md` une fois ce périmètre traité (il documente encore ce chantier comme non commencé, alors qu'il est déjà largement avancé)

## Phase 6 — Zones lourdes restantes

- [ ] Cadrer Tags/Folders avec un vrai passage de design Figma (pas juste du rattrapage de code)
- [ ] Cadrer Formulaires personnalisés
- [ ] Cadrer Gestion utilisateurs (aucun composant dédié existant — le plus gros chantier restant)

## Règles anti-éparpillement (rappel)

- Une seule zone "active" visible à la fois (ex. dans le milestone en cours).
- Ne pas ouvrir de PR de redesign sur une nouvelle zone tant que la zone active n'est pas "done".
- Toute idée hors zone active part au backlog `v2.9`, pas dans le sprint en cours.
