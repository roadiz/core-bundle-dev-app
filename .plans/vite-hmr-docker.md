# Réparer le HMR Vite du back-office Rozier dans la stack Docker

## Contexte

Le workflow actuel oblige à `pnpm build` + `make cache` + redémarrage des conteneurs à
chaque modification CSS, ce qui rend le travail sur `lib/Rozier/app/assets/css/` très lent.

Or **toute la plomberie HMR existe déjà** dans le projet :

- `lib/Rozier/vite-plugins/dev-manifest.ts` écrit un `manifest.dev.json` quand `vite` tourne
  en mode `serve`, avec des URLs absolues vers le serveur Vite et une entrée `@vite/client` ;
- `JsonManifestResolver` (`lib/RoadizRozierBundle/src/Vite/JsonManifestResolver.php`) préfère
  ce `manifest.dev.json` quand `kernel.debug` est vrai ;
- `base.html.twig:235` et `simple.html.twig:46` injectent déjà `manifest_script_tags('@vite/client')` ;
- le service `node` (profil `frontend`) publie déjà le port 5173 et monte `./lib/Rozier` +
  `./lib/RoadizRozierBundle/public`.

Le mécanisme est donc correct sur le papier, mais **trois bugs l'empêchent de fonctionner**.
Symptôme observé (back-office normal, aucun rechargement, sous Chrome) : le bug n°1.

### Bug 1 — le manifest est mis en cache Redis même en debug (cause du symptôme)

`JsonManifestResolver::getManifest()` lit `cache.app` (Redis) **avant** de regarder si
`manifest.dev.json` existe, et l'enregistre sans TTL ni invalidation sur mtime :

```php
$cacheItem = $this->cache->getItem('roadiz_rozier.vite.manifest');
if ($cacheItem->isHit()) { return $cacheItem->get(); }   // ← court-circuite tout
```

Quand on lance `docker compose up node`, Redis contient encore le manifest **de build**.
L'application continue donc à servir `/bundles/roadizrozier/main-*.js` : le navigateur ne
contacte jamais `:5173`, `@vite/client` n'est jamais chargé, aucun HMR. Exactement le
symptôme décrit. Inversement, quand on arrête `node`, le plugin supprime `manifest.dev.json`
(`clearOnClose`) mais Redis garde les URLs `:5173` → back-office cassé jusqu'au `make cache`.

### Bug 2 — l'origine générée est `http://0.0.0.0:5173`, bloquée par Chrome

`Dockerfile:362` lance `CMD ["pnpm", "dev", "--host", "0.0.0.0"]`, donc dans
`dev-manifest.ts` :

```ts
const host = server.config.server.host ?? 'localhost'   // ← vaut '0.0.0.0'
const origin = `${protocol}://${host}:${port}`          // ← http://0.0.0.0:5173
```

Chrome ≥ 133 bloque les requêtes vers `0.0.0.0` (protection Private Network Access) et Safari
ne le résout pas. Ce bug n'est pas encore visible aujourd'hui parce que le bug n°1 masque tout ;
il le deviendra dès que le n°1 sera corrigé. Cas voisin : `--host` sans valeur donne
`config.server.host === true` → `http://true:5173`.

### Bug 3 — `async` sur les balises `<script type="module">` casse l'ordre en dev

`RozierExtension::getManifestScriptTags()` émet `<script async type="module">`. En build,
Rollup fait que `main` importe le chunk `shared`, donc l'ordre est garanti par le graphe de
modules. En dev, `shared` et `main` sont **trois graphes indépendants** servis par Vite, et
`async` les fait s'exécuter dès réception : `main.js` (qui attend le jQuery global posé par
`shared.js` → `./jquery-bootstrap`) peut s'exécuter en premier et planter. `type="module"`
est déjà différé par défaut, `async` n'apporte rien et nuit ici.

### Résultat attendu

`docker compose up node` suffit : les modifications de `lib/Rozier/app/assets/css/**` et des
composants sont visibles instantanément dans le back-office sur `http://localhost:8681`, sans
build ni vidage de cache. `Ctrl-C` sur `node` fait repasser proprement sur les assets buildés.

---

## Modifications

### 1. `lib/RoadizRozierBundle/src/Vite/JsonManifestResolver.php` — ne pas cacher en debug

Court-circuiter le cache Redis lorsque `$this->debug` est vrai. Le fichier fait ~15 Ko : un
`json_decode` par requête en dev est négligeable, et c'est ce qui rend le basculement
dev ↔ build instantané.

Structure cible (extraire deux méthodes privées depuis `getManifest()`) :

```php
private function getManifest(): array
{
    // In debug mode, never cache: the Vite dev server creates and removes
    // manifest.dev.json on the fly, and a stale cache would pin the app
    // to the wrong asset origin.
    if ($this->debug) {
        return $this->readManifest($this->resolveManifestPath());
    }

    $cacheItem = $this->cache->getItem('roadiz_rozier.vite.manifest');
    if ($cacheItem->isHit()) {
        return $cacheItem->get();
    }

    $cacheItem->set($this->readManifest($this->resolveManifestPath()));
    $this->cache->save($cacheItem);

    return $cacheItem->get();
}

private function resolveManifestPath(): string { /* logique dev/build existante */ }

private function readManifest(string $manifestPath): array { /* file_exists + json_decode existants */ }
```

Conserver tel quel le repli « si `manifest.dev.json` n'existe pas, garder `manifest.json` »
et l'exception `%s manifest not found`.

### 2. `lib/Rozier/vite-plugins/dev-manifest.ts` — normaliser l'hôte de l'origine

Dans le callback `httpServer?.once('listening', …)`, remplacer le calcul de `host`/`origin` :

```ts
// 0.0.0.0 / :: / true are bind addresses, not addresses a browser can fetch.
// Chrome blocks 0.0.0.0 outright, so fall back to localhost.
const resolveHost = (host: string | boolean | undefined): string =>
    !host || true === host || '0.0.0.0' === host || '::' === host ? 'localhost' : host

const origin =
    config.server.origin || `${protocol}://${resolveHost(config.server.host)}:${port}`
```

Préférer `config.server.origin` quand il est défini permet de surcharger l'URL depuis
`vite.config.ts` (voir §3) sans retoucher le plugin.

### 3. `lib/Rozier/vite.config.ts` — expliciter le bind et l'origine

Dans le bloc `server` (actuellement `cors`/`port`/`strictPort` uniquement) :

```ts
server: {
    cors: true,
    host: true, // listen on all interfaces, required inside Docker
    // Make sure this port is the same as in Dockerfile and compose.yml
    port: 5173,
    strictPort: true,
    // URL the browser uses to reach this server (the app runs on another origin).
    origin: process.env.VITE_DEV_ORIGIN ?? 'http://localhost:5173',
},
```

`host: true` rend le `--host 0.0.0.0` du `CMD` (`Dockerfile:362`) redondant — le laisser en
place ne pose plus de problème une fois §2 appliqué. `VITE_DEV_ORIGIN` couvre les postes qui
n'utilisent pas `localhost:8681` (proxy, domaine custom) : il suffira d'ajouter
`environment: { VITE_DEV_ORIGIN: … }` au service `node` dans `compose.override.yml`.

### 4. `lib/RoadizRozierBundle/src/TwigExtension/RozierExtension.php` — retirer `async`

Dans `getManifestScriptTags()` :

```php
'<script type="module" src="%s"></script>',
```

### 5. `README.md` — mettre à jour « Backoffice frontend development »

La section dit aujourd'hui qu'il faut `make cache` après un rebuild. Après le §1, préciser
que le `make cache` + `docker compose restart app` ne concernent **que** le mode build
(`pnpm build`), et ajouter que `docker compose up node` bascule seul en HMR (le back-office
sur `http://localhost:8681` charge alors ses assets depuis `http://localhost:5173`).

Prérequis à mentionner : `./lib/Rozier:/app` masque le `node_modules` installé dans l'image,
donc `lib/Rozier/node_modules` doit exister côté hôte (`make build_assets` ou
`docker compose run --rm --no-deps --entrypoint= node pnpm install --frozen-lockfile`).

### 6. Copier ce plan dans `.plans/` du dépôt

Conformément à l'habitude de travail : déposer ce document dans `.plans/` (dossier déjà
présent, non suivi par Git) avant d'attaquer l'implémentation.

---

## Vérification

1. `make cache` **une fois** — indispensable : le cache Redis actuel contient encore l'ancien
   manifest, et le correctif §1 ne purge pas rétroactivement la clé
   `roadiz_rozier.vite.manifest` déjà écrite.
2. `docker compose up node` (profil `frontend` auto-activé en nommant le service).
   Vérifier dans les logs que Vite écoute bien sur 5173, et que
   `lib/RoadizRozierBundle/public/manifest.dev.json` est créé et contient
   `"file": "http://localhost:5173/@vite/client"` (et non `0.0.0.0`).
3. Ouvrir `http://localhost:8681/rz-admin`, afficher la source : les `<script type="module">`
   doivent pointer sur `http://localhost:5173/...`, sans `async`, et dans l'ordre
   `@vite/client`, `shared`, `main`. Console Chrome : aucune erreur réseau, `[vite] connected`.
4. Modifier `lib/Rozier/app/assets/css/components/rz-breadcrumb.css` (fichier déjà en cours de
   modification sur la branche) → le style doit changer sans rechargement de page.
5. Modifier `lib/Rozier/app/custom-elements/RzPopover.ts` → rechargement du module / de la page.
6. `Ctrl-C` sur `node`, puis recharger `http://localhost:8681/rz-admin` : le back-office doit
   repasser immédiatement sur les assets buildés, **sans** `make cache`.
7. Non-régression build : `make build_assets` puis recharger — les assets hashés sont servis
   depuis `/bundles/roadizrozier/`, et le manifest est de nouveau mis en cache Redis.
8. `make phpstan` et `make check` (les modifications PHP touchent un package publié).

## Notes

- Si les modifications de fichiers ne sont pas détectées (inotify non propagé), ajouter
  `server.watch: { usePolling: true, interval: 300 }` dans `vite.config.ts`. À ne faire qu'en
  cas de besoin réel : le polling est coûteux en CPU et le bind mount Linux remonte
  normalement les événements inotify.
- `lib/RoadizRozierBundle/` est un package open source publié : commits en anglais, format
  Conventional Commits (`fix(rozier): …`).
- Aucune nouvelle dépendance ni franchissement de couche : `make check-architecture` reste vert.
