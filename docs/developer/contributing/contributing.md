---
title: Contributing
---

# Contributing

If you want to contribute to Roadiz project by reporting issues or hacking code, let us thank you! You are awesome!

## Reporting issues

When you encounter an issue with Roadiz we would love to hear about it.
Because thanks to you, we can make the most awesome and stable CMS!

If you submit a bug report please include all information available to you, here are some things you can do :

- Try to simplify the things you are doing until getting a minimal set of actions reproducing the problem.
- Do not forget to join a screenshot or a trace of your error.

## Coding style

The code you contributed to the project should respect the guidelines defined in PHP *PSR2* standard.
If you install the requirements for devs by the command `composer update --dev`, you can use *phpcs* to check your code.

You can copy and paste the following command-lines to check and fix it easily :

```shell
php vendor/bin/php-cs-fixer fix --ansi -vvv
```

Please take those rules into account, we aim to have a clean codebase.
A coherent code-style will contribute to Roadiz stability.
Your code will be checked when we will be considering your pull requests.

## Naming view variables

Templates receive variables from two places, and each side keeps the conventions of the language it
is written in:

- **Controller render parameters are `camelCase`.** They are PHP array keys sitting next to PHP
  variables: `headPath`, `cancelPath`, `thumbnailFormat`, `nodeTypeName`.
- **Template options are `snake_case`.** They are the flags and slots an `include` or an `embed`
  consumes: `with_breadcrumb`, `with_nav_bar`, `nav_bar_template`, `available_translations`,
  `action_label`.

Some variables cross that boundary: set by Twig on some pages, injected by a controller on others.
Follow **the template that reads them**, not the caller that happens to set them. `breadcrumb_parents`
is fed both by `folders/head.html.twig` and by `FolderController`, and is read right next to
`with_breadcrumb`, so it stays `snake_case` on both sides:

```php
return $this->render('@RoadizRozier/admin/confirm_action.html.twig', [
    'headPath' => '@RoadizRozier/folders/head.html.twig', // controller parameter
    'breadcrumb_parents' => [/* … */],                    // head.html.twig option
]);
```

A few older templates predate this rule and still mix both styles in the same call. Do not copy them,
and do not rename them opportunistically either: a template variable is a public API, so renaming one
is a breaking change that belongs in a major release and in `UPGRADE.md`.

Finally, document the options a template expects in a `{# Variables: #}` comment at the top of the
file, the way `admin/head.html.twig` does. It is the only place a caller can discover them.

## Building a back-office breadcrumb

A page **never declares the root of its own section**. The section head
(`@RoadizRozier/<section>/head.html.twig`) does, and works out on its own whether the page *is* that
root by comparing the current route to the root's — which is why a listing page shows `Users` and not
`Users › Users`. A page only describes the levels **in between**, through `breadcrumb_parents`:

```twig
{# users/edit.html.twig — the head prepends "Users" #}
{% include '@RoadizRozier/users/head.html.twig' with {
    title: user.username,
} only %}

{# custom-form-fields/add.html.twig — one intermediate level #}
{% include '@RoadizRozier/custom-forms/head.html.twig' with {
    title: 'add.a.custom-form-field'|trans,
    breadcrumb_parents: [{
        label: customForm.displayName|truncate_title,
        url: path('customFormFieldsListPage', {customFormId: customForm.id}),
    }],
} only %}
```

Outside a section head — a confirmation page rendered through `admin/confirm_action.html.twig`, or a
section that has no head of its own — ask for the root instead of retyping it:

```twig
breadcrumb_parents: [
    breadcrumb_root('users'),
    { label: item.username, url: path('usersEditPage', {id: item.id}) },
],
```

```php
'breadcrumb_parents' => [
    $this->breadcrumbRoots->get('users'),
    ['label' => $user->getUserName(), 'url' => $this->generateUrl('usersEditPage', ['id' => $user->getId()])],
],
```

`breadcrumb_root()` and the `BreadcrumbRoots` service share one table of sections, in
`lib/RoadizRozierBundle/src/Breadcrumbs/BreadcrumbRoots.php`. Add an entry there rather than writing a
`{label, url}` hash by hand — that hash used to be duplicated forty times over.

On a confirmation page acting on a **tree entity** (`Node`, `Tag`, `Folder`), show the whole path down
to it, so the reader knows *which* of three folders named `2026` they are about to delete. That is what
`breadcrumb_trail()` / `BreadcrumbRoots::trailTo()` returns — root, ancestors, then the item itself:

```php
'breadcrumb_parents' => $this->breadcrumbRoots->trailTo('folders', $folder),
// Folders › Invoices › 2026 › Q1 › Delete Q1
```

```twig
'breadcrumb_parents': breadcrumb_trail('nodes', item),
```

Bulk confirmations (several items at once) pass nothing: there is no single item to point at.

Entries below the root are either a `{label, url}` hash or an entity a
`BreadcrumbsItemFactory` supports (`Node`, `NodesSources`, `Tag`, `Folder`, `Document`,
`Translation`), which the trail resolves for you.

`BreadcrumbRoots` covers the sections Rozier ships. A project adding its own back-office section
writes its root inline in the template that needs it — see
`docs/extensions/custom_backoffice_entry.md`.

## Static analysis

Then we use `phpstan` as a static code analyzer to check bugs and misuses before they occur :

```shell
php -d "memory_limit=-1" vendor/bin/phpstan analyse -c phpstan.neon
```
