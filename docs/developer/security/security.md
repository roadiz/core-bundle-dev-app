# Roadiz security system

Roadiz uses Symfony's security component to manage user authentication and authorization.

## Built-in roles
Roadiz comes with a set of built-in roles that can be used to manage access control in your back-office.
These roles are defined in the `config/packages/security.yaml` file.

| Role name                             | Description                                                          |
|:--------------------------------------|:---------------------------------------------------------------------|
| `ROLE_SUPERADMIN`                     | Inherits all roles in application                                    |
| `ROLE_BACKEND_USER`                   | Only grants access to backoffice and its user account                |
| `ROLE_ACCESS_VERSIONS`                | Grants tags, documents and nodes-sources versionning                 |
| `ROLE_ACCESS_ATTRIBUTES`              | Grants attributes creation and updates                               |
| `ROLE_ACCESS_ATTRIBUTES_DELETE`       | Grants attributes deletion                                           |
| `ROLE_ACCESS_CUSTOMFORMS`             | Grants custom-forms creation, updates and answers management         |
| `ROLE_ACCESS_CUSTOMFORMS_RETENTION`   | Grants custom-forms maximum retention date (feature flag)            |
| `ROLE_ACCESS_CUSTOMFORMS_DELETE`      | Grants custom-forms deletion                                         |
| `ROLE_ACCESS_DOCTRINE_CACHE_DELETE`   | Grants server caches clearing action                                 |
| `ROLE_ACCESS_DOCUMENTS`               | Grants documents uploading, embedding, updating and folders          |
| `ROLE_ACCESS_DOCUMENTS_LIMITATIONS`   | Grants access to documents copyright validation dates (feature flag) |
| `ROLE_ACCESS_DOCUMENTS_DELETE`        | Grants documents deletion                                            |
| `ROLE_ACCESS_DOCUMENTS_CREATION_DATE` | Grants access to document creation date (feature flag)               |
| `ROLE_ACCESS_GROUPS`                  | Grants user groups management                                        |
| `ROLE_ACCESS_NODE_ATTRIBUTES`         | Grants nodes attributes management (feature flag)                    |
| `ROLE_ACCESS_NODES`                   | Grants nodes-sources creation and edition                            |
| `ROLE_ACCESS_NODES_DELETE`            | Grants nodes and nodes-sources deletion                              |
| `ROLE_ACCESS_NODES_SETTING`           | Grants nodes settings edition and position in tree                   |
| `ROLE_ACCESS_NODES_STATUS`            | Grants node publication (status)                                     |
| `ROLE_ACCESS_NODETYPES`               | Grants node-types decoration and access definitions (Typescript)     |
| `ROLE_ACCESS_REDIRECTIONS`            | Grants access to redirections                                        |
| `ROLE_ACCESS_SETTINGS`                | Grants access to settings                                            |
| `ROLE_ACCESS_TAGS`                    | Grants tags creation and updates, and nodes tagging                  |
| `ROLE_ACCESS_TAGS_DELETE`             | Grants tags deletion                                                 |
| `ROLE_ACCESS_TRANSLATIONS`            | Grants translations management                                       |
| `ROLE_ACCESS_USERS`                   | Grants users administration                                          |
| `ROLE_ACCESS_USERS_DELETE`            | Grants users deletion                                                |
| `ROLE_ACCESS_USERS_DETAIL`            | Grants users details edition (feature flag)                          |
| `ROLE_ACCESS_WEBHOOKS`                | Grants webhooks management                                           |
| `ROLE_ACCESS_LOGS`                    | Grants logs access on dashboard and on nodes history                 |
| `ROLE_ACCESS_REALMS`                  | Grants realms management (creation, update, deletion)                |
| `ROLE_ACCESS_REALM_NODES`             | Grants attaching nodes to existing realms                            |
| `ROLE_ACCESS_FONTS`                   | Grants fonts management (optional bundle)                            |
| `ROLE_ALLOWED_TO_SWITCH`              | Grants right to impersonate another user (Symfony default)           |

## Users and groups

You can attach these roles directly to user accounts or create **Groups** to manage roles in bulk.

## Custom voters

### Node and NodesSources voter

We recommend using the `NodeVoter` to check permissions on **nodes** and **nodes-sources**, it supports user *chroot* feature. 
This voter allows you to check permissions on nodes and nodes-sources with the following actions:

- `CREATE`
- `DUPLICATE`
- `CREATE_AT_ROOT`
- `SEARCH`
- `READ`
- `READ_AT_ROOT`
- `EMPTY_TRASH`
- `READ_LOGS`
- `EDIT_CONTENT`
- `EDIT_TAGS`
- `EDIT_REALMS`
- `EDIT_SETTING`
- `EDIT_STATUS`
- `EDIT_ATTRIBUTE`
- `DELETE`

```php
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeVoter;

#...

$this->denyAccessUnlessGranted(NodeVoter::EDIT_CONTENT, $node);
```
