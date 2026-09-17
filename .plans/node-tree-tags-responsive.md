# Tags de l'arbre de nœuds : même repli responsive que le fil d'Ariane

> Plan implémenté le 2026-09-16, vérifié dans Storybook (story `Tree/WithTags`) et dans le
> back-office sur `/rz-admin/nodes/tree/387/16`.


## Contexte

Sur `/rz-admin/nodes/tree/{nodeId}/{translationId}` (stack tree), chaque ligne affiche ses tags via le
même macro que le fil d'Ariane, `macros/rz_overflow_list.html.twig` (embed dans
`widgets/nodeTree/singleNode.html.twig:102-131`, `visibleCount: 2`). Mais l'option `responsive`
ajoutée pour le fil d'Ariane n'y est pas activée : 2 badges fixes, le reste dans le popover, quelle
que soit la largeur. Sur ligne étroite, les badges ne se compriment pas (`min-width: auto`, texte
qui **revient à la ligne** faute de `--rz-badge-label-white-space`) et écrasent le titre.

Décisions prises avec l'utilisateur :

1. **Le titre garde une largeur minimale**, les tags se replient un par un en dessous. Le titre
   continue de se tronquer (ellipsis) comme aujourd'hui.
2. **Plafond serveur : 6 tags** visibles (au lieu de 2). Le JS descend en dessous quand la ligne
   manque de place, et remonte, jamais au-delà de ce que le serveur a rendu.
3. **Le compteur du bouton reste à jour** (nombre de tags repliés).

## Pourquoi `OverflowList.ts` ne s'applique pas tel quel

Deux contrats DOM différents pour le même macro :

| | fil d'Ariane | tags de l'arbre |
|---|---|---|
| items visibles | `<li>` **frères après** le wrapper (`visibleCount: 0`, rendus par le macro appelant) | **dans** le wrapper, **avant** `<rz-popover>` (`visible_items` du macro) |
| items repliés | le **début** du milieu | la **fin** de la liste |
| élément à masquer quand rien n'est replié | le wrapper `<li>` (sinon chevron orphelin) | le `<rz-popover>` (le wrapper contient les badges) |
| `gap` entre items | aucun | `gap: var(--spacing-6xs)` sur le wrapper |
| enfant élastique dans la ligne | aucun | `.rz-tree__item__label { flex-grow: 1 }` |

La ligne observée est bien `wrapper.parentElement` dans les deux cas : `.rz-tree__item__node__inner`
a `width: 100%` (`rz-tree.css:115`), sa largeur ne dépend pas du contenu, et le label `flex-grow: 1`
absorbe le mou : pas de boucle ResizeObserver, sans CSS supplémentaire.

## Modifications

### 1. `lib/Rozier/app/utils/OverflowList.ts` — généraliser, sans second composant

- **Côté des items inline** : lire `overflow-inline-side` (`before` | `after`).
  - `after` (actuel) : N frères après le wrapper ; `items = [...repliés, ...inline]` ; replier =
    `list.append(wrapper.nextElementSibling)`, déplier = `wrapper.after(list.lastElementChild)`.
  - `before` (nouveau) : N frères précédant `context` (le `<rz-popover>`) dans le wrapper ;
    `items = [...inline, ...repliés]` ; replier = `list.prepend(context.previousElementSibling)`,
    déplier = `context.before(list.firstElementChild)`.
  - Cible du `hidden` quand `visible === n` : `wrapper` en `after`, `context` en `before`.
- **Gap** : `gap = parseFloat(getComputedStyle(conteneurDesItems).columnGap) || 0` (wrapper en
  `before`, row en `after`). Coût d'un item = `offsetWidth + gap`.
- **Mesure en trois états**, dans le même tick (aucune peinture intermédiaire), la row à
  `width: max-content` :
  1. tout inline, popover visible → `widths[i]` ;
  2. rien inline, popover visible → `fixedWidth = row.offsetWidth` (mesuré, plus d'arithmétique
     fragile) ;
  3. popover masqué → `fixedNoPopover`.
  Décision : tout tient sans popover si `n ≤ maxVisible && fixedNoPopover + Σcoûts − (n ? gap : 0) ≤ available` ;
  sinon `k` maximal tel que `fixedWidth + Σ_k coûts ≤ available`, borné par `maxVisible`.
- **Enfants élastiques** : pendant la mesure, tout enfant direct de la row (hors wrapper et items)
  dont `flexGrow > 0` reçoit temporairement `style.width = '0'`, puis est restauré. Sa contribution
  devient son `min-width` CSS : c'est ainsi que « le titre garde un minimum » sans que le composant
  connaisse le label. Sans effet sur le fil d'Ariane (aucun enfant de `<ol>` n'a de flex-grow).
- **Compteur** : si l'attribut `overflow-count` est présent sur `context`, `setVisible()` écrit
  `n − k` dans `.rz-overflow-list__button .rz-button__label` (créé en tête du bouton s'il manque :
  `rz_button.html.twig:48` ne rend pas le span pour un libellé `0`).
- Garder : ResizeObserver sur la row, rAF coalescé, garde popover ouvert / item focalisé,
  remesure sur `document.fonts` `loadingdone`, `destroy()`.
- Commentaire `ponytail:` sur la mesure : 3 reflows forcés par instance, une instance par ligne
  taguée ; acceptable pour un stack tree (dizaines de lignes). Piste si ça pèse : mesurer toutes
  les instances dans la même frame via une file statique.

### 2. `templates/macros/rz_overflow_list.html.twig`

- Quand `responsive` :
  - `overflow-inline = visible_count > 0 ? visible_items|length : responsive_inline`
  - `overflow-inline-side = visible_count > 0 ? 'before' : 'after'`
  - `overflow-count` si `button.label` n'est pas défini (libellé par défaut = compteur).
  - `hidden` quand `overflow_items is empty` : sur le wrapper si `visible_count == 0`, sinon sur
    `<rz-popover>`.
- Mettre à jour le commentaire d'en-tête (`responsiveInline` n'est utile qu'avec `visibleCount: 0`,
  le compteur est désormais synchronisé).

### 3. `templates/widgets/nodeTree/singleNode.html.twig`

`visibleCount: 6`, `responsive: true`. Rien d'autre.

### 4. `lib/Rozier/app/assets/css/components/rz-tree.css`

```css
.rz-tree__item__tags {
    /* ... existant ... */
    --rz-badge-label-white-space: nowrap; /* un badge ne revient jamais à la ligne : il se replie */
}

/* Réserve de titre, uniquement sur les lignes taguées : l'aside (280px) n'a pas de tags
   et ne doit pas déborder sur les nœuds profonds. Lue par OverflowList pendant la mesure. */
.rz-tree__item__node__inner:has(> .rz-tree__item__tags) > .rz-tree__item__label {
    min-width: 120px;
}
```

Valeur 120px à ajuster à l'œil ; c'est le seul « bouton » du dispositif.

### 5. Storybook — `lib/Rozier/stories/RzTree.stories.ts`

- `Item` gagne `tags?: string[]` ; `itemNodeRenderer` rend le wrapper
  `div.rz-tree__item__tags.rz-overflow-list` avec les badges (`rz-badge`, voir
  `macros/rz_badge.html.twig` pour le markup : `span.rz-badge__label`), le `<rz-popover
  overflow-responsive overflow-inline="N" overflow-inline-side="before" overflow-count>` et le bouton
  `rz-overflow-list__button` (réutiliser `rzPopoverRenderer` et `rzButtonRenderer` comme dans
  `app/utils/storybook/renderer/rzBreadcrumb.ts`).
- Story `WithTags` : 8 tags sur une ligne, décorateur `resize: horizontal` (même motif que
  `RzBreadcrumb.stories.ts` → `Responsive`), `play` : titre jamais sous 120px, badges inline +
  compteur = total, aucun débordement de la row.

### Inchangé

- `rz_breadcrumb.html.twig`, `rz_breadcrumb_item.html.twig`, `rz-breadcrumb.css`,
  `rzBreadcrumb.ts` (renderer) : le mode `after` reste leur contrat. La story `Responsive` du fil
  d'Ariane sert de non-régression.
- `RzPopover.ts` : déjà branché sur `overflow-responsive`.

## Vérification

1. `pnpm exec eslint`, `prettier --check`, `tsc --noEmit` sur les fichiers touchés ;
   `bin/console lint:twig` sur les deux templates.
2. Storybook (déjà démarré, port 6006) via le script CDP du scratchpad (`cdp.mjs`, Chrome headless,
   le MCP navigateur n'étant pas disponible) :
   - `components-breadcrumb--responsive` et `--collapsed` : mêmes résultats qu'avant (non-régression
     du mode `after`).
   - `components-tree--with-tags` à 1200 / 700 / 400 px : nombre de badges inline décroissant,
     compteur du bouton = 8 − inline, `label.offsetWidth ≥ 120`, `inner.scrollWidth ≤ inner.clientWidth + 1`.
3. `docker compose run --rm node pnpm build`, puis **`make cache`** (manifeste Vite en Redis),
   `docker compose restart app`, puis `nginx`.
4. Back-office réel sur `/rz-admin/nodes/tree/387/16` : nécessite un compte. Même procédé que pour
   le fil d'Ariane (compte super-admin temporaire `users:create`, supprimé ensuite) — à confirmer,
   sinon vérification manuelle par l'utilisateur : redimensionner la fenêtre, tags qui rentrent
   dans le popover un par un, titre jamais écrasé, compteur juste, drag & drop d'une ligne taguée
   (reconnexion des `<rz-popover>`), ouverture/fermeture d'un sous-arbre (ligne `display: none` →
   remesure au premier resize).
5. Ne pas embarquer les artefacts régénérés de `lib/RoadizRozierBundle/public/` dans la MR.

## Risques

- **Flash au chargement** sur ligne étroite : le serveur rend 6 tags, le JS replie après
  chargement du chunk. Même compromis que le fil d'Ariane, dans l'autre sens.
- **Coût de mesure** : 3 reflows par ligne taguée au chargement et à chaque chargement de police.
  Voir le commentaire `ponytail:`.
- **`:has()`** : déjà utilisé dans `rz-tree.css:25` pour le collapse, donc supporté par les cibles.
- **6 tags par défaut** change l'affichage sur grand écran pour tout le monde (décision explicite).
