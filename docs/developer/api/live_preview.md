# Live preview

On a headless Roadiz install the public website is another application (Nuxt, Astro, …). Rozier can
frame it, in preview mode, right next to the node-source edit form: editors see the rendering of what
they edit without leaving the back-office, and the page reloads on each save.

The column only appears when `roadiz_core.customPublicScheme` (or `customPreviewScheme`) is set. A
classic Roadiz is untouched.

## Configuration

```yaml
# config/packages/roadiz_core.yaml
roadiz_core:
    # The public site is a separate application. Sends the back-office "see website" and node
    # "view" / "preview" buttons to the front instead of to the API host.
    # Scheme + authority only, no trailing slash — the node path is appended to it.
    customPublicScheme: '%env(string:APP_FRONTEND_URL)%'
```

Set it **per environment**: a value hardcoded in the committed `.env` sends production previews to a
development host.

```yaml
# config/packages/roadiz_rozier.yaml
roadiz_rozier:
    live_preview:
        # Defaults
        enabled: true
        # Viewport widths offered in the column. The iframe is laid out at the chosen width then
        # scaled down to fit, so the front renders its real breakpoint instead of always rendering mobile.
        viewport_widths: [375, 768, 1440]
```

## Prerequisites on the front

**Content-Security-Policy.** The back-office frames another origin, so its responses must allow it:

```nginx
add_header Content-Security-Policy "frame-ancestors 'self'; frame-src 'self' https://www.example.com";
```

Listing only `http://localhost:3000` makes the feature work in development and silently fail in
staging and production.

**Preview mode.** The column loads the existing `nodesSourcesPreviewRedirect` route: it checks the
editor's rights, mints a preview JWT and redirects to
`{customPreviewScheme or else customPublicScheme}/{node path}?token=…&_preview=1&_no_cache=1`. The front
must forward `token` and `_preview` to the API.

**Blocks.** Blocks have no URL of their own, so editing a block previews its closest reachable
ancestor (`NodesSources::getFirstReachableParent()`) and tells the front which block is being edited,
using the protocol below. No extra serialization group is needed to match that block: in JSON-LD
every node-source carries an `@id` IRI ending with its id (e.g. `/api/basic_blocks/12`), even block
types that declare no operation. A front requesting plain `application/json` gets no `@id`: add the
`id` serialization group to its `*_get_by_path` operations instead.

## `postMessage` protocol

The column and the framed front talk over `window.postMessage`.
Like any Roadiz output consumed by a front, this contract follows the Roadiz backward-compatibility
policy: a breaking change only ships in a major release and is listed in `UPGRADE.md`. Messages
therefore carry no version field; fronts must read them defensively.

The whole flow, from opening the column to highlighting the edited block:

```
  Editor            Rozier (back-office)             Roadiz (PHP)               Front (iframe)
    │                        │                            │                            │
    │ opens the column       │                            │                            │
    ├───────────────────────►│                            │                            │
    │                        │ iframe.src =               │                            │
    │                        │ /rz-admin/…/preview/{page} │                            │
    │                        ├───────────────────────────►│                            │
    │                        │                            │ checks EDIT_CONTENT,       │
    │                        │                            │ mints a preview JWT        │
    │                        │◄───────────────────────────┤                            │
    │                        │ 302 → {front}/{page path}?token=…&_preview=1            │
    │                        ├────────────────────────────────────────────────────────►│
    │                        │                            │  API call with the token   │
    │                        │                            │◄───────────────────────────┤
    │                        │                            │  page + blocks, drafts too │
    │                        │                            ├───────────────────────────►│
    │                        │                            │                            │ renders,
    │                        │                            │                            │ hydrates
    │                        │  postMessage { type: 'roadiz:ready' }                   │
    │                        │◄────────────────────────────────────────────────────────┤
    │                        │ checks origin + source     │                            │
    │                        │                            │                            │
    │                        │ ── only when a block is edited ─────────────────────────│
    │                        │  postMessage { type: 'roadiz:inspect', nodeSourceId }   │
    │                        ├────────────────────────────────────────────────────────►│
    │                        │                            │                            │ checks origin
    │                        │                            │                            │ + source,
    │                        │                            │                            │ finds the block,
    │                        │                            │                            │ scrolls to it
    │                        │                            │                            │
    │ saves the form         │                            │                            │
    ├───────────────────────►│ reloads the iframe: the whole flow starts again         │
```

- `{page}` is the edited node-source when it has a URL, or its closest reachable ancestor when it is a
  block. `nodeSourceId` is always the node-source being edited.
- The front talks first: Rozier never sends `roadiz:inspect` before `roadiz:ready`, so the front's
  listener is always registered in time.

| Direction | Message | When |
|---|---|---|
| front → Rozier | `{ type: 'roadiz:ready' }` | once the previewed page is hydrated |
| Rozier → front | `{ type: 'roadiz:inspect', nodeSourceId: string }` | in answer, only when a block is being edited |

Rules, on both sides:

- `targetOrigin` is always explicit, never `'*'`. Rozier uses `customPreviewScheme`, or `customPublicScheme`
  when the former is empty or not set.
- The receiver checks `event.origin` **and** `event.source` before reading `event.data`.
- The front announces on hydration, not on iframe `load`: a `load`-time message races the front's own
  listener registration.
- A message missing an expected field is ignored, never an error: the page must keep rendering.

Minimal front side. `BACK_OFFICE_ORIGIN` is the scheme + authority of the Roadiz back-office
(e.g. `https://api.example.com`), `highlightBlock()` is yours to write:

```ts
if (window.parent !== window) {
    window.addEventListener('message', (event) => {
        // Check who sends before reading what is sent.
        if (event.origin !== BACK_OFFICE_ORIGIN || event.source !== window.parent) return
        if (event.data?.type !== 'roadiz:inspect') return
        // Read defensively: a missing field must not break the page.
        const nodeSourceId = event.data.nodeSourceId
        if (typeof nodeSourceId !== 'string' || nodeSourceId === '') return
        // Match the block whose `@id` ends with `/${nodeSourceId}`.
        highlightBlock(nodeSourceId)
    })
    // Send once hydrated, not on load: the listener above must already be registered.
    window.parent.postMessage({ type: 'roadiz:ready' }, BACK_OFFICE_ORIGIN)
}
```
