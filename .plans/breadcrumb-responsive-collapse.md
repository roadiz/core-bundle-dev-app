# Repli dynamique du fil d'Ariane selon la place disponible

> Plan implémenté le 2026-09-16, avec un renversement du point de départ décidé en cours de
> route : le serveur affiche **6 ancêtres par défaut** (`max_visible = 6`, seul l'excédent est
> replié) et le JS **réduit** ce nombre quand la largeur manque, puis le restaure, sans jamais
> dépasser l'état serveur. Le plan ci-dessous décrit l'idée initiale (rendu replié à 3, JS qui
> déplie) ; la mécanique de mesure, l'anti-boucle et le markup unique restent tels quels.
> Rédigé à la suite de la refonte du fil d'Ariane (`.plans/breadcrumb-refactor.md`).

## Contexte

Le fil d'Ariane replie ses maillons du milieu au-delà d'un seuil **fixe** de 3 ancêtres, décidé côté
serveur (`macros/rz_breadcrumb.html.twig`, `{% set collapse_from = 3 %}`). C'est insensible à la
largeur réelle : sur un grand écran un fil de 5 maillons tiendrait sans problème mais reste replié, et
sur mobile 3 maillons peuvent déjà s'écraser.

Objectif : **plafond de 6 ancêtres affichés**, et en dessous de ce plafond, replier davantage quand la
largeur manque. Le serveur rend l'état replié (pas de débordement visible, fil utilisable sans JS) ; le
client mesure et déplie s'il y a la place.

### Décisions prises

1. **Plafond de 6**, pas seulement un point de départ : au-delà, on replie même sur grand écran.
2. **Rendu initial replié**, le JS déplie ensuite. Aucun débordement visible, et le popover natif
   reste fonctionnel sans JS.
3. **Le seuil serveur reste à 3**, le plafond de 6 est appliqué côté client. Les deux se complètent :
   3 est la ligne de base conservatrice, 6 le maximum que le client déplie. La racine et le dernier
   ancêtre étant rendus hors du composant, le plafond qu'on lui passe est **4** (6 − 2).

### Deux options écartées, et pourquoi

**Généraliser `rz-overflow-list` en custom element** — impossible en l'état, pour deux raisons
indépendantes :

- Les maillons visibles du fil doivent être des `<li>` **frères** dans le `<ol>`. Or le wrapper de
  `rz-overflow-list` est **un seul** `<li>` contenant le popover : déplier depuis l'intérieur de ce
  `<li>` casserait la structure de liste.
- `customElements.define(name, ctor, {extends})` lie un `is=` à **un seul** tag de base, et
  `defineLazyElement` déduit ce tag du premier élément rencontré dans le DOM. Or le wrapper est `li`
  pour le fil et `div` pour le node-tree : le comportement dépendrait de la page. Un
  `<rz-overflow-list>` autonome dans un `<ol>` serait par ailleurs du HTML invalide.

**Un custom element dédié `rz-breadcrumb` sur le `<nav>`** — inutile : le `<rz-popover>` déjà présent
dans le markup est par construction **pile à la frontière de repli** et connaît déjà son `[popover]`.
Un élément de plus n'ajouterait qu'un niveau d'indirection.

---

## A. Un seul markup d'item, deux styles selon le contexte

C'est le cœur du changement, et ce qui rend le JS trivial.

Un maillon a aujourd'hui **deux formes** (`includes/rz_breadcrumb_item.html.twig`) :
`<a class="rz-breadcrumb__item">` dans la piste, `<a class="rz-dropdown__item">` dans le popover.
Déplacer un item d'un état à l'autre supposerait donc de réécrire sa structure en JS.

Or cette divergence n'a **rien de sémantique** : `.rz-dropdown__item` n'apporte que du visuel. Le
docblock du partial dit déjà que la forme popover *recopie* `rz_dropdown_menu` au lieu de la
réutiliser. On déplace cette recopie du Twig vers le CSS :

- **`includes/rz_breadcrumb_item.html.twig`** : supprimer la branche `in_popover` et sa variable. Une
  seule forme partout, `<li class="rz-breadcrumb__list-item"><a class="rz-breadcrumb__item">`.
- **`rz-breadcrumb.css`** : une douzaine de lignes donnant aux items situés sous
  `.rz-overflow-list__list` le look d'une entrée de menu (padding, `min-height`, radius, hover, typo
  `label-sm`), en miroir de `.rz-dropdown__item`. Sélecteur descendant : aucun effet sur les badges du
  node-tree.
- **`rz_breadcrumb.html.twig`** : retirer `itemTemplateContext: { in_popover: true }`.

Le JS n'a plus qu'à déplacer le même `<li>` : trois lignes, aucune connaissance du contenu. Les badges
du node-tree se déplaceraient tels quels le jour venu.

---

## B. Le composant

**Créer `lib/Rozier/app/utils/OverflowList.ts`** — la mesure et la décision.
**Modifier `app/custom-elements/RzPopover.ts`** : dans `connectedCallback`, si l'attribut
`overflow-responsive` est présent, `import('~/utils/OverflowList')` en dynamique puis instancier ;
`destroy()` dans `disconnectedCallback`. Import dynamique pour ne rien peser sur les dizaines d'autres
popovers. C'est le motif `RzBadge` → `utils/Tooltip.ts` et `RzPopover` → `utils/Popover.ts` du dépôt.

Attributs, préfixe cohérent avec les `popover-*` existants :

| attribut | rôle |
|---|---|
| `overflow-responsive` | opt-in. Absent → zéro JS, node-tree strictement inchangé. |
| `overflow-max-visible` | nombre max d'items sortis du popover. Le fil passe `4`. |

`macros/rz_overflow_list.html.twig` gagne deux options `responsive` et `maxVisible` qui ne posent ces
attributs que si `responsive`. Rien d'autre ne bouge dans le macro.

---

## C. Mesure — le piège à connaître

`.rz-breadcrumb__list-item` et `.rz-breadcrumb__item` portent `min-width: 0` : les items
**rétrécissent** au lieu de déborder. `list.scrollWidth > nav.clientWidth` est donc **faux** alors même
que les libellés s'écrasent. Une mesure de débordement ne marche pas ici ; il faut des largeurs
**naturelles**.

**Passe de mesure**, une fois puis sur `document.fonts.ready` (sans quoi les largeurs du premier rendu
sont fausses, la première peinture utilisant une police de repli) :

1. poser `width: max-content` sur la ligne → plus aucun rétrécissement ;
2. insérer tous les items gérés inline ;
3. lire `offsetWidth` de chaque `<li>`, du wrapper, et le total de la ligne — le chevron est un
   `::before` du `<li>`, donc **inclus**, et la liste n'a pas de `gap` : simple somme ;
4. restaurer l'état **de façon synchrone dans le même tour** : le navigateur ne peint que l'état
   final, pas de clignotement ;
5. `fixed = totalLigne − Σ largeursGérées − largeurWrapper`.

**Décision**, à chaque resize, arithmétique pure (aucune mesure de layout hormis `row.clientWidth`) :
parcourir les items du dernier vers le premier — on replie depuis le début du milieu, donc on déplie
depuis la fin, les ancêtres les plus proches l'emportant — en accumulant tant que ça rentre, en
réservant la largeur du bouton sauf si tout est déplié, et en s'arrêtant à `maxVisible`.

Puis déplacer exactement `k` nœuds : `wrapper.after(li)` pour déplier en prenant le dernier de la
liste, `list.append(li)` pour replier en prenant `wrapper.nextElementSibling`. Insérer toujours juste
après le wrapper préserve l'ordre. `k === 0` = état serveur, zéro mutation ; `k === n` →
`wrapper.hidden = true`.

---

## D. Anti-boucle et cycle de vie

Modèle `RzGeotag.ts` : observer créé en `connectedCallback`, `disconnect()` en
`disconnectedCallback`, garde d'initialisation.

- `ResizeObserver` sur le `<ol>`, pas sur le nav ni sur les items.
- **`flex-grow: 1` sur `.rz-breadcrumb`** — le point non évident, et la vraie garde. Sans lui le nav
  est dimensionné par son contenu : `clientWidth` vaut la largeur *utilisée*, jamais la largeur
  *disponible*, donc on ne déplierait jamais, et chaque dépliage élargirait le nav → nouvelle
  notification → boucle. Avec `flex-grow: 1` (les `min-width: 0` et `overflow: hidden` sont déjà là),
  la largeur observée devient indépendante de nos mutations : la boucle est cassée
  **structurellement**, pas par une rustine.
- Coalescence en `requestAnimationFrame`, un seul pending ; sortie immédiate si la largeur n'a pas
  changé. L'algorithme est idempotent, donc il converge même en cas de passe parasite.
- Ne rien faire si le popover est ouvert (`:popover-open`) ou si `this.contains(document.activeElement)`
  — déplacer un nœud focalisé peut perdre le focus, et vider un popover sous le curseur est
  désagréable. Recalcul au `beforetoggle` de fermeture.
- Pas de `MutationObserver` : un fil d'Ariane est statique sur la durée d'une page.

**`rz-overflow-list.css`** doit recevoir `.rz-overflow-list[hidden] { display: none }` : `[hidden]` est
une règle UA, battue par n'importe quelle règle auteur posant un `display` — et le wrapper en reçoit un
de son contexte (`.rz-breadcrumb__list-item` est `display: flex`). Sans ça, masquer le wrapper une fois
tout déplié ne ferait rien.

**`rz-breadcrumb.css`** : scoper le chevron à la piste
(`.rz-breadcrumb__list > .rz-breadcrumb__list-item + .rz-breadcrumb__list-item::before`), sinon il
apparaît aussi entre les entrées du popover.

---

## E. Storybook — une duplication qui disparaît

Comme le composant replie autant qu'il déplie, la position initiale des items dans le DOM lui est
indifférente. `app/utils/storybook/renderer/rzBreadcrumb.ts` perd `COLLAPSE_FROM`,
`ItemOptions.inPopover`, la branche `collapsed` et la moitié `inPopover` de `itemRenderer` : il ne reste
qu'à rendre tous les parents inline, insérer le wrapper après le premier, puis l'item courant. Le seuil
n'est alors **plus dupliqué nulle part** — c'est un défaut connu, signalé aujourd'hui en commentaire
dans les deux fichiers.

---

## Fichiers

**Créé** — `lib/Rozier/app/utils/OverflowList.ts`
**Modifiés** — `app/custom-elements/RzPopover.ts`, `app/assets/css/components/rz-breadcrumb.css`,
`app/assets/css/components/rz-overflow-list.css`, `app/utils/storybook/renderer/rzBreadcrumb.ts`,
`stories/RzBreadcrumb.stories.ts`, `templates/includes/rz_breadcrumb_item.html.twig`,
`templates/macros/rz_breadcrumb.html.twig`, `templates/macros/rz_overflow_list.html.twig`
**Inchangé** — `widgets/nodeTree/singleNode.html.twig` : `visibleCount: 2` est un plafond de densité,
pas un problème de place. Le responsive est opt-in, il ne l'active pas.

---

## Vérification

1. **Story `Responsive`** dans `RzBreadcrumb.stories.ts`, avec un décorateur
   `<div style="resize: horizontal; overflow: hidden; width: 700px">` : on tire la poignée et on voit
   plier/déplier en direct. C'est la vérification de fond, elle coûte quatre lignes (l'addon viewport
   n'est pas installé).
2. **Une `play` function** sur cette story, sans dépendance (`throw new Error` nu — il n'y a aucun test
   runner JS dans ce paquet) : après deux `requestAnimationFrame`, vérifier qu'aucun item inline n'est
   écrasé (`el.scrollWidth <= el.clientWidth + 1`), que racine / dernier ancêtre / item courant sont
   inline, et que le nombre d'ancêtres inline ne dépasse pas 6.
3. **Manuel dans le back-office** : nœud profond (`Page level 5`), redimensionner la fenêtre, vérifier
   le repli et le dépliage, ouvrir le popover, puis `Tab` — un seul arrêt par niveau, l'item ne devant
   exister qu'une fois dans le DOM.
4. **Effet de bord du `flex-grow`** : relire `RzPageHeader.stories.ts` et une page d'admin sans boutons
   d'action, c'est le seul changement à portée globale.
5. `make test`, puis `docker compose run --rm node pnpm build` et redémarrage de `app` et `nginx`.
   Attention : le build réécrit `lib/RoadizRozierBundle/public/`, dont les artefacts sont **suivis par
   git** — ne pas les embarquer par accident dans la MR.

---

## Risques

- **Flash au chargement** : rendu serveur replié puis dépliage après chargement du JS. C'est le prix
  assumé du rendu initial replié. Ne pas le masquer par un `visibility: hidden` levé en JS, ça
  casserait le cas sans-JS.
- **Fils de 3 ancêtres ou moins** : aucun popover n'est rendu, donc aucun repli possible même à
  l'étroit — les libellés se chevauchent, comme aujourd'hui. Hors périmètre ; le correctif juste serait
  `text-overflow: ellipsis` sur `.rz-breadcrumb__item`, à traiter séparément.
- **Sens de repli** : un seul sens est à implémenter (depuis le début). Le node-tree n'étant pas
  responsive, il n'y a pas de second sens à faire coexister. Le jour où un appelant voudra replier
  depuis la fin, c'est une poignée de lignes (ancre et extrémité inversées).
- **Compteur du bouton** : le macro affiche `overflow_items|length` par défaut ; le fil passe
  `label: ''`, donc rien à synchroniser. Un futur appelant responsive qui garderait le compteur le
  verrait devenir faux — à signaler en commentaire sur l'option `responsive`.
