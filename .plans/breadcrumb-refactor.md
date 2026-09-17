# Refonte du breadcrumb Rozier

## Contexte

Le fil d'Ariane du back-office a été introduit par vagues (`80fabc51` → `371efd18` → `80124801`) et porte les cicatrices de cette accumulation. Aujourd'hui :

- **un seul point de rendu** (`macros/rz_page_header.html.twig:40-80`) mais **quatre noms de variables** pour alimenter la même donnée (`breadcrumb.parent`, `breadcrumb_parents`, `breadcrumbParent`, `parentBreadcrumb`) et **deux formes d'items** (hash littéral vs entité Doctrine) mélangées dans le même tableau ;
- **19 clés de traduction `*-breadcrumb` fantômes** injectées telles quelles dans `aria-label` sans `|trans` : les lecteurs d'écran annoncent littéralement « users-breadcrumb ». C'est une régression RGAA directe, sur un projet où la conformité est exigée à la livraison ;
- **le maillon courant est un `<a href="">`** qui recharge la page au clic ;
- **aucune gestion du débordement**, alors que le breadcrumb partage sa ligne avec les boutons d'action et que le CSS `.rz-breadcrumb__popover-content` (déjà écrit) n'est émis par aucun template ;
- **un include mort** (`nodes/translateWaiting.html.twig:18` → `@RoadizRozier/nodes/breadcrumb.html.twig`, fichier inexistant) qui fait planter la page d'attente de traduction.

Objectif : un contrat de données unique, un rendu accessible, un débordement géré avec les briques existantes du design system — sans ajouter de JS ni de dépendance.

Cycle 2.8, déjà porteur de BC breaks Twig documentés dans `UPGRADE.md` : le moment est le bon.

---


## A. Rendu — extraire et normaliser la macro

**Nouveau fichier `lib/RoadizRozierBundle/templates/macros/rz_breadcrumb.html.twig`**, la macro sort de `rz_page_header.html.twig` (ce n'est pas une préoccupation de page-header, et les 4 blocs `{% block breadcrumb %}` identiques deviennent un seul include).

Boucle actuelle : deux branches `<li>` dupliquées, avec troncature appliquée d'un côté seulement et icône décidée par `parent.type == 'listing'` ici / `'list' in breadcrumbsItem.url` là (reniflage de sous-chaîne d'URL). À remplacer par **une seule branche** :

```twig
{% for parent in breadcrumb.parent|default([]) %}
    {% set item = (parent is iterable and parent.url is defined) ? parent : getBreadcrumbsItem(parent) %}
    {% if item %}
        <li class="rz-breadcrumb__list-item">
            <a class="rz-breadcrumb__item" href="{{ item.url }}">
                {% if item.type == 'home' %}<span class="rz-icon-ri--home-2-fill" aria-hidden="true"></span>{% endif %}
                {% if item.type == 'listing' %}<span class="rz-icon-ri--list-unordered" aria-hidden="true"></span>{% endif %}
                {{- item.label|truncate_title -}}
            </a>
        </li>
    {% endif %}
{% endfor %}
```

Corrections portées par cette réécriture :

1. `truncate_title` appliqué aux deux formes (aujourd'hui `parent.label` ligne 50 y échappe) ;
2. le `if item` absorbe le `null` que `nodes/add.html.twig:19` injecte via un ternaire — aujourd'hui ça marche par accident ;
3. icônes décoratives en `aria-hidden="true"` ;
4. plus de `'list' in url`.
5. `rz-button__icon` n'a rien à faire ici (classe d'un autre composant) → retirée.

**Maillon courant** : `<a href="" aria-current="page">` → `<span class="rz-breadcrumb__item" aria-current="page">`. C'est le pattern WAI-ARIA, et la story `Default` le fait déjà correctement — le Twig est le seul à diverger.

**`aria-label`** : supprimer la clé `label` du contrat et coder en dur `{{ 'breadcrumb'|trans }}`. `aria-label` ne sert qu'à départager plusieurs landmarks de même rôle ; il n'y a **jamais** deux breadcrumbs sur une page. Les 19 variantes n'apportent rien à personne et coûtent 19 clés xlf + 19 lignes d'appelants. Effet de bord gratuit : le bug du `label: 'users-breadcrumb'` copié-collé dans `custom-forms/{head,list}.html.twig` disparaît sans qu'on y touche.

**`rz_page_header.html.twig:2`** : `block('breadcrumb')` est évalué une fois pour le test de vacuité puis une seconde fois pour le rendu. `{% set breadcrumb_html = block('breadcrumb') %}` en amont, puis tester et afficher la variable.

---

## B. Traductions

- Supprimer la trans-unit `nodes-breadcrumb` (`messages.en.xlf:5247`, `messages.fr.xlf:5247` — cette dernière n'a même pas de `<target>`).
- Ajouter une seule unit `breadcrumb` : `Breadcrumb` / `Fil d'Ariane`.
- Retirer `label:` des ~35 sites qui le passent (mécanique, cf. inventaire §C).

---

## C. Contrat de données — un seul nom, une seule forme

**Nom retenu : `breadcrumb_parents`** (snake_case, cohérent avec `with_breadcrumb`). `parentBreadcrumb` serait 8 renommages de moins mais casserait la convention sur ce qui est une API de template publique.

| Nom actuel | Sites | Action |
|---|---|---|
| `parentBreadcrumb` | 10 Twig (`*/delete.html.twig`, `groups/remove*`) + 5 PHP (`TranslationController:197`, `SettingController:341`, `UserRoleController:118`, `FolderController:166`, `DocumentController:104`) | → `breadcrumb_parents` |
| `breadcrumbParent` | `documents/head.html.twig:29`, `documents/upload.html.twig:9`, `documents/embed.html.twig:9` | → `breadcrumb_parents` |
| `breadcrumb_parents` clés `displayName` | `custom-form-fields/{add,editBase}`, `custom-form-field-attributes/list`, `attributes/groups/edit`, `node-types-decorators/add` | `displayName:` → `label:` |

**Forme d'item unique** : `{ label, url, type? }` où `type` ∈ `listing` \| `home` \| absent. Le renommage `displayName` → `label` fait disparaître la boucle de conversion copiée-collée dans les 6 heads de la famille §2.1 (`users`, `groups`, `attributes`, `realms`, `custom-forms`, `node-types`) — ces heads se réduisent alors au même squelette que `settings/head.html.twig`.

Ajouter partout le garde-fou manquant : `breadcrumb_parents|default([])` (aujourd'hui une page passant `with_breadcrumb: true` sans parents lève en mode strict).

**Côté PHP** : `BreadcrumbsItem` (`lib/RoadizRozierBundle/src/Breadcrumbs/BreadcrumbsItem.php`) — remplacer la propriété `enabled` (**morte**, lue nulle part) par `?string $type = null`, et faire porter `'home'` par ce champ plutôt que par le booléen `home`. Les 6 factories (`Node`, `NodesSources`, `Tag`, `Folder`, `Document`, `Translation`) s'alignent : les deux branches de la macro exposent alors exactement les mêmes champs.

**Ce qu'on n'ajoute pas** : les ~10 factories manquantes (`User`, `Group`, `Setting`, `Realm`, `Webhook`…). Aucun template ne passe ces entités au breadcrumb — ils passent des hashs de 3 lignes, et une factory de 35 lignes pour remplacer un hash de 3 lignes est une perte sèche. Les factories n'existent que pour les entités à **chaîne de parents récursive** (`Node`, `Tag`, `Folder`) ; les autres sections sont plates. À rouvrir seulement si une section plate gagne une hiérarchie.

---

## D. Débordement — réutiliser `rz_overflow_list`

Rien à écrire : `macros/rz_overflow_list.html.twig` + le custom element `rz-popover` existent et tournent déjà sur le node-tree (`widgets/nodeTree/singleNode.html.twig:102`).

- Dans `rz_breadcrumb.html.twig`, si `breadcrumb_parents|length > 3`, passer les maillons intermédiaires dans un `{% embed '@RoadizRozier/macros/rz_overflow_list.html.twig' %}` avec `visibleCount: 1` (racine visible, dernier parent visible, le reste sous le bouton « … »). `visibleCount` est un entier côté serveur, pas de JS.
- Attention en reprenant la story : elle pose `data-popover-placement` / `data-popover-offset` alors que `rz-popover` lit `popover-placement` / `popover-offset` (cf. `macros/rz_overflow_list.html.twig:74-76`). La story `WithPopover` ne positionne donc rien aujourd'hui.
- Nettoyer au passage `rz_overflow_list.html.twig` : `{% import %}` dupliqué lignes 1-2, bloc `{% set visible_count %}` mort lignes 7-11.

Corriger aussi l'écrasement de la ligne partagée avec les boutons : ni `.rz-page-header__row` ni `.rz-breadcrumb` n'ont de `min-width: 0`, d'où le `max-width: calc(100% - 4px)` marqué « Temporaly fix » dans `rz-page-header.css:14-15`. Ajouter `min-width: 0` sur les deux et supprimer le contournement.

On conserve `truncate_title` (45 car.) : déjà en place, coût nul, et il protège le HTML en amont du CSS.

---

## E. CSS — `lib/Rozier/app/assets/css/components/rz-breadcrumb.css`

1. **Séparateur** : les trois blocs `background-image` dupliqués (`@media (prefers-color-scheme: dark)` + `[data-theme="light"]` + `[data-theme="dark"]`, lignes 20-40) partent au profit de la classe d'icône officielle `.rz-icon-ri--arrow-drop-right-line` (mask + `background-color: currentColor`, `icons/ri.css:1`). Le dark mode devient gratuit, et le `color: var(--content-on-light-primary)` de la ligne 27 — aujourd'hui inopérant sur un `background-image` — reprend son sens.
2. **RTL** : `transform: scaleX(-1)` sur le séparateur sous `[dir="rtl"]`, espacements en propriétés logiques (`margin-inline`). `simple.html.twig:3` et `base.html.twig` posent déjà `dir="rtl"` selon la locale.
3. **Tokens morts à brancher** : `--breadcrumb-content-hover` sur `:hover`, `--breadcrumb-content-selected` sur `[aria-current="page"]` (`theme.css:25-28`). Aujourd'hui l'item courant n'a aucun style distinct.
4. **Responsive** : `--text-breadcrumb-md-*` (14px) sous `@media (--screen-lg-min)`, comme `rz-page-header.css:32` — la paire de tokens `md` n'est consommée nulle part.
5. **Legacy mort** : supprimer `app/assets/less/breadcrumb/breadcrumb.less` et son import `less/style.less:116`. Vérifié : aucun template n'utilise `.content-breadcrumb` ni `.node-breadcrumb-item`.

---

## F. Storybook

- Extraire le renderer inliné dans `stories/RzBreadcrumb.stories.ts:37-97` vers `app/utils/storybook/renderer/rzBreadcrumb.ts`, comme `rzPopover.ts` / `rzHeader.ts` / `rzTablist.ts`.
- Aligner sur le markup réel : icônes `home`/`listing`, item courant en `<span>`, variante overflow avec les bons attributs `popover-*`.
- `RzPageHeader.stories.ts:54-76` simule un breadcrumb avec un `<nav><ol><li>` nu sans classes : le faire consommer le renderer partagé.
- La faute `"Fil d'arianne"` disparaît avec la suppression de l'arg `ariaLabel`.

---

## G. Bugs à corriger dans la foulée

| Fichier | Problème |
|---|---|
| `nodes/translateWaiting.html.twig:18` | `{% include '@RoadizRozier/nodes/breadcrumb.html.twig' %}` — **le fichier n'existe pas**, la page lève. Remplacer par l'include du head. |
| `widgets/nodeTree/contextualMenu.html.twig:154`, `nodes/actionsMenu.html.twig:46` | `getBreadcrumbsItem(node.parent).url` déréférence un retour potentiellement `null`. Garder le résultat en variable et tester. |
| `documents/head.html.twig:28` | `type: 'listing'` posé à la racine du hash `breadcrumb`, où la macro ne le lit pas. Mort, à retirer. |
| 8 heads (`users`, `groups`, `custom-forms`, `settings`, `settingGroups`, `redirections`, `translations`, `realms`) | Incluent `admin/head.html.twig` **sans `with`** et comptent sur la fuite de contexte. Rendre le passage explicite pendant le renommage — sinon tout passage ultérieur en `only` cassera ces pages. |

**Hors périmètre, à noter dans la MR** : le flag `with_breadcrumb` (52 appelants, opt-in, ignoré par `settings/head.html.twig` et `nodes/head.html.twig`) reste tel quel. Et `lib/RoadizCoreBundle/src/Api/Breadcrumbs/` est le breadcrumb **API Platform du front public** — homonyme, aucun lien, ne pas y toucher.

---

## Fichiers critiques

**Créés** — `templates/macros/rz_breadcrumb.html.twig`, `app/utils/storybook/renderer/rzBreadcrumb.ts`
**Modifiés en profondeur** — `templates/macros/rz_page_header.html.twig`, `app/assets/css/components/rz-breadcrumb.css`, `src/Breadcrumbs/BreadcrumbsItem.php` (+ les 6 factories), `stories/RzBreadcrumb.stories.ts`
**Modifiés mécaniquement** — les 15 `*/head.html.twig`, les ~23 templates qui construisent `breadcrumb: {...}`, les 10 `*/delete.html.twig`, les 5 contrôleurs listés en §C
**Supprimés** — `app/assets/less/breadcrumb/breadcrumb.less` (+ import `style.less:116`)

---

## Vérification

1. `make test` — inclut le lint Twig (qui attrapera l'include mort de `translateWaiting`), PHPStan niveau 8, PHP-CS-Fixer, Deptrac, PHPUnit.
2. `docker compose run --rm node pnpm build`, puis `docker compose restart app && docker compose restart nginx` (cf. README « Backoffice frontend development »).
3. `docker compose up storybook` — comparer `Components/Breadcrumb` (Default + overflow) en thème clair, sombre et système.
4. Parcours manuel, une page par famille de construction :
   - **chaîne réelle** : édition d'un nœud profond (≥ 4 niveaux), une page de tag, un dossier de documents ;
   - **racine seule** : dashboard, liste d'utilisateurs, liste de réglages ;
   - **`breadcrumb_parents`** : `custom-form-fields/add`, `attributes/groups/edit` ;
   - **`confirm_action`** : suppression d'un utilisateur, retrait d'un rôle de groupe ;
   - **page réparée** : `nodes/translateWaiting`.
5. Débordement : réduire la fenêtre à ~480px sur un nœud profond — le popover « … » doit apparaître, la ligne ne doit pas pousser les boutons d'action hors écran.
6. A11y : inspecter le `<nav>` (`aria-label` traduit, item courant en `<span aria-current="page">`, icônes `aria-hidden`). Faire relire par un référent RGAA (Timothé ou Manuel).
7. Passer une locale RTL pour vérifier le retournement du séparateur.
