# Building classic websites with Twig

Roadiz uses the same [`WebReponse` object](../api/web_response.md) and system to render pages using Twig templates.
By default, Roadiz will look for a `Controller` class in your project `src/Controller` directory that matches the requested
node-type. 

For example, if you have a node-type named `Article`, Roadiz will look for a controller named `App\Controller\ArticleController` in the `src/Controller` directory.

If not, the Roadiz default controller will be used to render the node-type template: `RZ\Roadiz\CoreBundle\Controller\DefaultNodeSourceController`.
Default controller can be configured in your `config/packages/roadiz_core.yaml` file:

```yaml
# config/packages/roadiz_core.yaml
roadiz_core:
    defaultNodeSourceController: 'RZ\Roadiz\CoreBundle\Controller\DefaultNodeSourceController'
```

## Default nodes-sources rendering controller

Here is an example of a default controller that renders a node-source using the `WebResponse` object:

```php
<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Controller;

use ApiPlatform\Metadata\IriConverterInterface;
use RZ\Roadiz\CoreBundle\Api\DataTransformer\WebResponseDataTransformerInterface;
use RZ\Roadiz\CoreBundle\Api\Model\WebResponseInterface;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
final class DefaultNodeSourceController extends AbstractController
{
    public function __construct(
        private readonly WebResponseDataTransformerInterface $webResponseDataTransformer,
        private readonly IriConverterInterface $iriConverter,
    ) {
    }

    public function __invoke(Request $request, NodesSources $nodeSource): Response
    {
        $request->attributes->set('_translation', $nodeSource->getTranslation());
        $request->attributes->set('_locale', $nodeSource->getTranslation()->getPreferredLocale());
        $iri = $this->iriConverter->getIriFromResource($nodeSource);
        $request->attributes->set('_resources', $request->attributes->get('_resources', []) + [$iri => $iri]);

        $data = $this->webResponseDataTransformer->transform($nodeSource, WebResponseInterface::class);

        return $this->render('@RoadizCore/nodeSource/default.html.twig', [
            'webResponse' => $data,
        ]);
    }
}
```

This controller will be used to render the node-source template located in `@RoadizCore/nodeSource/default.html.twig`.
You can override this template in your own theme by creating a file at `templates/bundles/RoadizCoreBundle/nodeSource/default.html.twig`.

```twig
{% extends "base.html.twig" %}

{% block title %}{{ webResponse.item.title }}{% endblock %}

{% macro walk_block(block) %}
    <li>{{ block.item.title }}</li>
    {% if block.children|length %}
        <ul>
            {% for child in block.children %}
                {{ _self.walk_block(child) }}
            {% endfor %}
        </ul>
    {% endif %}
{% endmacro %}

{% block body %}
    {% if webResponse.item %}
        <h1><a href="{{ path(webResponse.item) }}">Page: {{ webResponse.item.title }}</a></h1>
        {% if webResponse.item.content is defined %}
            {{ webResponse.item.content|markdown }}
        {% endif %}
        {% for image in webResponse.item.images %}
            {{ image|display({
                'width': 300,
                'picture': true,
            }) }}
        {% endfor %}
    {% endif %}

    {%- if not webResponse.HidingBlocks and webResponse.blocks|length -%}
        <h2>Blocks</h2>
        <ul>
            {% for block in webResponse.blocks %}
                {{ _self.walk_block(block) }}
            {% endfor %}
        </ul>
    {%- endif -%}
{% endblock %}
```

## Creating a controller for a node-type

To create a controller for a specific node-type, you can create a new controller class in your `src/Controller` directory. For example, if you want to create a controller for the `Article` node-type, you can create a file named `ArticleController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use ApiPlatform\Metadata\IriConverterInterface;
use RZ\Roadiz\CoreBundle\Api\DataTransformer\WebResponseDataTransformerInterface;
use RZ\Roadiz\CoreBundle\Api\Model\WebResponseInterface;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
final class ArticleController extends AbstractController
{
    public function __construct(
        private readonly WebResponseDataTransformerInterface $webResponseDataTransformer,
        private readonly IriConverterInterface $iriConverter,
    ) {
    }

    public function __invoke(Request $request, NodesSources $nodeSource): Response
    {
        $request->attributes->set('_translation', $nodeSource->getTranslation());
        $request->attributes->set('_locale', $nodeSource->getTranslation()->getPreferredLocale());
        $iri = $this->iriConverter->getIriFromResource($nodeSource);
        $request->attributes->set('_resources', $request->attributes->get('_resources', []) + [$iri => $iri]);

        $data = $this->webResponseDataTransformer->transform($nodeSource, WebResponseInterface::class);

        return $this->render('@RoadizCore/nodeSource/article.html.twig', [
            'webResponse' => $data,
        ]);
    }
}
```

## Provide globlal variables to Twig templates

You can provide global variables to your Twig templates. For example, you can can add the menus entries to all your templates by creating a Twig extension in your `src/Twig` directory:

```php
<?php

declare(strict_types=1);

namespace App\Twig;

use App\TreeWalker\MenuNodeSourceWalker;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Api\Controller\TranslationAwareControllerTrait;
use RZ\Roadiz\CoreBundle\Api\TreeWalker\TreeWalkerGenerator;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

final class AppExtension extends AbstractExtension implements GlobalsInterface
{
    use TranslationAwareControllerTrait;

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ManagerRegistry $managerRegistry,
        private readonly PreviewResolverInterface $previewResolver,
        private readonly TreeWalkerGenerator $treeWalkerGenerator,
    ) {
    }

    #[\Override]
    protected function getManagerRegistry(): ManagerRegistry
    {
        return $this->managerRegistry;
    }

    #[\Override]
    protected function getPreviewResolver(): PreviewResolverInterface
    {
        return $this->previewResolver;
    }

    private function getMenus(): array
    {
        $request = $this->requestStack->getMainRequest();

        return $this->treeWalkerGenerator->getTreeWalkersForTypeAtRoot(
            'Menu',
            MenuNodeSourceWalker::class,
            $this->getTranslation($request),
            3
        );
    }

    #[\Override]
    public function getGlobals(): array
    {
        return [
            'menus' => $this->getMenus(),
        ];
    }
}
```

Then in your Twig templates, you can access the `menus` variable:

```twig
{% macro walk_menu(children) %}
    {% for walker in children %}
        <li>
            {% if walker.item.isReachable %}
                <a href="{{ path(walker.item) }}">{{ walker.item.title }}</a>
            {% else %}
                <span>{{ walker.item.title }}</span>
            {% endif %}
            <ul>
                {{ _self.walk_menu(walker.children) }}
            </ul>
        </li>
    {% endfor %}
{% endmacro %}
<nav class="nav-horizontal">
    <ul>
        {{ _self.walk_menu(menus.mainMenuWalker.children) }}
    </ul>
</nav>

<div class="container">{% block body %}{% endblock %}</div>
```
