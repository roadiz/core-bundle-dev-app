---
title: Realms — access-restricted content areas
---

# Realms

A **Realm** is a named access-control zone applied to one or more nodes in the content tree.
When a node belongs to a realm, the API `WebResponse` endpoint reports which realms govern it
and whether the current request is allowed to see them — so your frontend can decide whether to
render restricted content, hide blocks, or redirect the user to an authentication screen.

::: tip Where realms are enforced
Realm authentication is enforced wherever `WebResponseDataTransformerInterface` is used:
on the **API WebResponse** endpoint, and on any **Twig-rendered controller** that extends or
invokes `DefaultNodeSourceController` (which calls the same transformer internally).
Custom Twig controllers that do **not** go through `DefaultNodeSourceController` must call
`RealmResolver::denyUnlessGranted()` themselves if they need realm enforcement.
:::

---

## Core concepts

### Authentication types

Each realm uses one of three mechanisms to decide whether a visitor is granted access:

| Type constant | Value | How access is granted |
|---|---|---|
| `TYPE_PLAIN_PASSWORD` | `plain_password` | Visitor provides the shared password in the request |
| `TYPE_ROLE` | `bearer_role` | Visitor's JWT/session token includes the required Symfony role |
| `TYPE_USER` | `bearer_user` | Visitor's JWT/session token matches one of the realm's allowed users |

### Behaviour modes

When access is **denied**, a realm's behaviour controls what happens:

| Behaviour constant | Value | Effect |
|---|---|---|
| `BEHAVIOUR_NONE` | `none` | Realm presence is reported but no access restriction is enforced |
| `BEHAVIOUR_DENY` | `deny` | API responds with **401 Unauthorized** |
| `BEHAVIOUR_HIDE_BLOCKS` | `hide_blocks` | API responds normally but `hidingBlocks: true` is set in WebResponse; blocks are not rendered by the transformer |

### Inheritance types

When you attach a realm to a node you choose how it propagates to child nodes:

| Inheritance constant | Value | Behaviour |
|---|---|---|
| `INHERITANCE_NONE` | `none` | Realm applies only to the exact node it is attached to |
| `INHERITANCE_AUTO` | `auto` | Realm is inherited automatically by all descendant nodes (default) |
| `INHERITANCE_ROOT` | `root` | Realm marks only the root of an inheritance subtree; children inherit it via async processing |

Inheritance changes are processed asynchronously through Symfony Messenger
(`ApplyRealmNodeInheritanceMessage`, `CleanRealmNodeInheritanceMessage`).

---

## Managing realms in the back office

### Required roles

| Role | What it grants |
|---|---|
| `ROLE_ACCESS_REALMS` | Create, edit and delete realm definitions |
| `ROLE_ACCESS_REALM_NODES` | Attach and detach nodes from existing realms |

### Creating a realm

1. Navigate to **Realms** in the back-office sidebar.
2. Click **Add a realm** and fill in the form:
   - **Name** — unique human-readable identifier (auto-generates a serialization group slug)
   - **Type** — choose `plain_password`, `bearer_role` or `bearer_user`
   - **Behaviour** — choose `none`, `deny` or `hide_blocks`
   - **Password** — (plain_password type only) the shared secret that clients must send
   - **Role** — (bearer_role type only) the Symfony role string, e.g. `ROLE_PREMIUM`
   - **Users** — (bearer_user type only) one or more Roadiz users
   - **Serialization group** — optional; when set, extra API fields gated behind this group name are exposed only to granted visitors

### Attaching a node to a realm

1. Open any node in the node tree editor.
2. Go to the **Realms** tab on the node settings panel.
3. Select the realm and choose the inheritance type.
4. Save — the back office fires `NodeJoinedRealmEvent` and schedules inheritance propagation if needed.

To detach, click the delete button next to the realm entry on the same tab.
This fires `NodeLeftRealmEvent` and triggers cleanup of inherited realm nodes.

---

## How authentication works in API requests

### Plain-password realms

Clients must provide the shared password on every request. Two methods are supported:

**Preferred — `Authorization` header** (password never appears in server logs):

```http
GET /api/web_response_by_path?path=/members-area
Authorization: PasswordQuery mysecretpassword
```

**Legacy — query parameter** (avoid; passwords appear in access logs and browser history):

```http
GET /api/web_response_by_path?path=/members-area&password=mysecretpassword
```

The `Authorization` header scheme name (`PasswordQuery`) is the value returned by
`Realm::getAuthenticationScheme()` and is also present in the `WWW-Authenticate` challenge
sent with 401 responses.

::: warning Security note
Passwords are stored as **bcrypt hashes** in the database. The plain-text value is
never retrievable after saving. Existing passwords set before this hashing was introduced
remain working via a timing-safe comparison fallback — re-save them through the admin UI
to upgrade them to bcrypt hashes.
:::

### Bearer-role realms

The visitor must be authenticated (e.g. via JWT) and their token must carry the required role.
No extra header is needed — `RealmVoter` consults Symfony's `AccessDecisionManager`.

```http
GET /api/web_response_by_path?path=/premium-content
Authorization: Bearer <jwt-token>
```

### Bearer-user realms

Same as bearer-role: the visitor must be authenticated and their user identifier must match
one of the realm's configured users.

```http
GET /api/web_response_by_path?path=/vip-section
Authorization: Bearer <jwt-token>
```

---

## WebResponse integration

When a node has realms attached, the `WebResponse` payload includes a `realms` array and a
`hidingBlocks` flag:

```json
{
    "@context": "/api/contexts/WebResponse",
    "@id": "/api/web_response_by_path?path=/members-area",
    "@type": "WebResponse",
    "item": { ... },
    "blocks": [],
    "realms": [
        {
            "@type": "Realm",
            "@id": "/api/realms/1",
            "type": "plain_password",
            "behaviour": "hide_blocks",
            "name": "Members area",
            "authenticationScheme": "PasswordQuery"
        }
    ],
    "hidingBlocks": true
}
```

- `realms` — the realms attached to this node that the current visitor has **not** been
  granted access to. If the visitor is granted, the realm does not appear here.
- `hidingBlocks` — `true` when at least one `hide_blocks` realm denied the visitor. Your
  frontend should render a paywall or login prompt instead of the block content.

### Enabling realms on a custom WebResponse

Your `WebResponse` model must implement `RealmsAwareWebResponseInterface`:

```php
<?php

declare(strict_types=1);

namespace App\Api\Model;

use RZ\Roadiz\CoreBundle\Api\Model\BlocksAwareWebResponseInterface;
use RZ\Roadiz\CoreBundle\Api\Model\RealmsAwareWebResponseInterface;
use RZ\Roadiz\CoreBundle\Api\Model\WebResponseInterface;
use RZ\Roadiz\CoreBundle\Api\Model\WebResponseTrait;

final class WebResponse implements
    WebResponseInterface,
    BlocksAwareWebResponseInterface,
    RealmsAwareWebResponseInterface
{
    use WebResponseTrait;
}
```

Your `DataTransformer` must then call `injectRealms()` from
`RealmsAwareWebResponseOutputDataTransformerTrait` during the transform step.
This method:

1. Resolves all realms attached to the node.
2. For each realm, calls `RealmResolver::isGranted()` — which invokes `RealmVoter`.
3. Collects **denied** realms into `WebResponse::$realms`.
4. Sets `hidingBlocks = true` if any denied realm has `BEHAVIOUR_HIDE_BLOCKS`.
5. Throws `UnauthorizedHttpException` (401) if any denied realm has `BEHAVIOUR_DENY`.

---

## Serialization groups

A realm can carry an optional **serialization group** name (auto-derived from the realm name
unless set manually). When a visitor is **granted** access to such a realm, that group name is
added to the API normalization context by `RealmSerializationGroupNormalizer`.

This lets you gate additional API fields behind realm access:

```yaml
# config/api_resources/web_response.yaml
resources:
    App\Api\Model\WebResponse:
        operations:
            page_get_by_path:
                normalizationContext:
                    groups:
                        - nodes_sources
                        - web_response
                        # 'members_area' group fields are injected automatically
                        # when the visitor is granted the 'Members area' realm
```

Define your node-type fields under the custom serialization group using `#[Groups(['members_area'])]`
on the relevant properties. Unauthenticated visitors will receive the response without those fields.

---

## Events

| Event class | Fired when |
|---|---|
| `RZ\Roadiz\CoreBundle\Event\Realm\NodeJoinedRealmEvent` | A node is attached to a realm |
| `RZ\Roadiz\CoreBundle\Event\Realm\NodeLeftRealmEvent` | A node is detached from a realm |

Both events expose the `RealmNode` entity via `$event->getRealmNode()`.

```php
<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use RZ\Roadiz\CoreBundle\Event\Realm\NodeJoinedRealmEvent;
use RZ\Roadiz\CoreBundle\Event\Realm\NodeLeftRealmEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class RealmSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            NodeJoinedRealmEvent::class => 'onNodeJoinedRealm',
            NodeLeftRealmEvent::class   => 'onNodeLeftRealm',
        ];
    }

    public function onNodeJoinedRealm(NodeJoinedRealmEvent $event): void
    {
        $realmNode = $event->getRealmNode();
        // $realmNode->getNode(), $realmNode->getRealm(), $realmNode->getInheritanceType()
    }

    public function onNodeLeftRealm(NodeLeftRealmEvent $event): void
    {
        $realmNode = $event->getRealmNode();
    }
}
```

---

## Frontend integration example

The following shows a minimal Nuxt 3 / Vue 3 pattern for handling realm-gated pages.

```typescript
// composables/useWebResponse.ts
const route = useRoute()
const config = useRuntimeConfig()

const { data, error } = await useFetch('/api/web_response_by_path', {
    params: { path: route.path },
    headers: realmPassword
        ? { Authorization: `PasswordQuery ${realmPassword}` }
        : {},
})

if (error.value?.statusCode === 401) {
    // Prompt the visitor for the realm password
}

if (data.value?.hidingBlocks) {
    // Render a paywall instead of block content
}
```

The `realms` array in the response tells you which realms are blocking access and their
`authenticationScheme` tells you how to authenticate (`PasswordQuery` vs `Bearer`):

```typescript
for (const realm of data.value?.realms ?? []) {
    if (realm.authenticationScheme === 'PasswordQuery') {
        // Show a password input
    } else {
        // Redirect to login / JWT refresh
    }
}
```
