---
title: Extending Roadiz
---

# Extending Roadiz

## Add back-office entry

At first, create a controller into your theme folder, for example `src/Controller/Admin/AdminController`.
```php
namespace App\Controller\Admin;

use Themes\Rozier\RozierApp;
use Symfony\Component\HttpFoundation\Request;

class AdminController extends RozierApp
{
    public function listAction(
        Request $request
    ) {
        return $this->render(
            'admin/test.html.twig',
            $this->assignation
        );
    }
}
```

If you look at this example you can see the class extends `RozierApp` class.
This will enable you to "inject" your code into Rozier Back-stage DOM and Style.

Now let\'s have a look to your twig template file `templates/admin/test.html.twig`.

```twig
{% extends '@Rozier/layout.html.twig' %}

{% block customStyles %}
<style>
    /* Custom styles here */
</style>
{% endblock %}

{% block customScripts %}
<script>
    /* Custom Scripts here */
</script>
{% endblock %}

{% block content %}
<section class="content-global add-test">
    <header class="content-header header-test header-test-edit">
        <h1 class="content-title test-add-title">{% trans %}Test admin{% endtrans %}</h1>
    </header>

    <article class="content content-test">
        <p>This page is created from MyTheme to show you how to extend backoffice features.</p>
    </article>
</section>
{% endblock %}
```

The first line is for inheriting from Rozier base template, you can notice that we explicitly choose [\@Rozier]{.title-ref} namespace.

The two next blocks are made for you to add some CSS or Javascript.
For CSS, the block `customStyle` can be use to link an external file with a `<link>` tag, the path must be something like that `asset('static/css/customstyle.css', 'MyTheme')`, or add directly some CSS with \"\<style\>\" tag.
For JS, the block `customScripts` work as is, just link an external JS file or write your `<script>` tag.

Then create your own content, do not hesitate to give a look at Rozier back-stage theme Twig files to use the right DOM structure.
For simple features, you wouldn't have to extend JS nor CSS if you follow the same HTML coding style.

## Linking things together

Add the route in the theme `config/routes.yaml` file.

In this case the route will be:
```yaml
adminTestPage:
    # Setting your path behind rz-admin will activate Firewall
    path: /rz-admin/test
    defaults:
        _controller: App\Controller\Admin\AdminController::listAction
```

### Inject your own entries in back-stage

The last thing to do is to add your new admin entry in the back-office menu.

Go to your `config/packages/roadiz_rozier.yaml` and add your own `entries`:

```yaml
roadiz_rozier:
    entries:
        # ...
        customAdmin:
            name: customAdmin
            route: adminTestPage
            icon: uk-icon-cube
            roles: ~
```

If you want to have a category and sub-entries, just change the path at `null` value and create your `subentries` array as described in the next example:
```yaml
roadiz_rozier:
    entries:
        # ...
        customAdmin:
            name: customAdmin
            route: ~
            icon: uk-icon-cube
            roles: ~
            subentries:
                customAdminPage:
                    name: 'customAdmin page'
                    route: adminTestPage
                    icon: uk-icon-cube
                    roles: ~
```

You can restrict buttons to users with specific roles. Just replace `roles: ~` with `roles: [ 'ROLE_ACCESS_NODES' ]`.
You can even create your own roles to take full power of Roadiz extension system.

::: warning
Adding roles in `roadiz_rozier.entries` service will only restrict buttons display in Rozier backstage interface.

To really protect your controllers from unwanted users add `$this->validateAccessForRole('ROLE_ACCESS_MY_FEATURE');` at the first line of your back-ofice controller's actions.

This will kick non-granted users from your custom back-office parts. Give a look at Rozier theme controllers to see how we use it.
:::
