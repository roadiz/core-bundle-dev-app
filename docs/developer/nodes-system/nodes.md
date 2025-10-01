# Nodes

## Handling nodes and their hierarchy

By default, if you use Entities API methods or traversing Twig filters, Roadiz will automatically handle security parameters such as `node.status` and `preview` mode.

```php
// Secure method to get node-sources
// Implicitly check node.status
$this->nodeSourceApi->getBy([
    'node.nodeTypeName' => $blogPostType->getName(),
    'translation' => $translation,
], [
    'publishedAt' => 'DESC'
]);
```

This first code snippet is using *Node-source API*. This will automatically check if the current user is logged in and if preview mode is *ON* to display or not *unpublished nodes*.

```php
// Insecure method to get node-sources
// Doctrine raw method will get all node-sources
$this->managerRegistry->getRepository(NSBlogPost::class)->findBy([], [
    'publishedAt' => 'DESC',
    'translation' => $translation,
]);
```

This second code snippet uses standard Doctrine *Entity Manager* to directly grab node-sources by their entity class. This method does not check any security and will return every node-source, **even unpublished, archived, and deleted ones**.

## Hierarchy

To traverse node-source hierarchy, the easiest method is to use *Twig* filters on your `nodeSource` entity. Filters will implicitly set `translation` from the origin node-source.

```twig
{% set children = nodeSource|children %}
{% set nextSource = nodeSource|next %}
{% set prevSource = nodeSource|previous %}
{% set parent = nodeSource|parent %}

{% set children = nodeSource|children({
    'node.visible': true
}) %}
```

::: warning  
All these filters will take care of publication status and translation, **but not publication date-time nor visibility**.
:::

```twig
{% set children = nodeSource|children({
    'node.visible': true,
    'publishedAt': ['>=', date()],
}, {
    'publishedAt': 'DESC'
}) %}

{% set nextVisible = nodeSource|next({
    'node.visible': true
}) %}
```

If you need to traverse the node-source graph from your controllers, you can use the *Entity API*. Moreover, the Node-source API allows you to filter using custom criteria if you choose a specific `NodeType`.

```php
$children = $this->nodeSourceApi->getBy([
    'node.parent' => $nodeSource,
    'node.visible' => true,
    'publishedAt' => ['>=', new \DateTime()],
    'translation' => $nodeSource->getTranslation(),
],[
    'publishedAt' => 'DESC'
]);
```

::: warning
Browsing your node graph (calling children or parents) could be very greedy and unoptimized if you have lots of node-types. Internally, *Doctrine* will *inner-join* every node-source table to perform polymorphic hydration. So, make sure you filter your queries by one `NodeType` as much as possible with `nodeSourceApi` and `node.nodeTypeName` criteria.
:::

```php
// Here Doctrine will only join NSPage table to NodesSources
$children = $this->nodeSourceApi->getBy([
    'node.nodeTypeName' => 'Page',
    'node.parent' => $nodeSource,
    'node.visible' => true,
    'publishedAt' => ['>=', new \DateTime()],
    'translation' => $nodeSource->getTranslation(),
],[
    'publishedAt' => 'DESC'
]);
```

## Visibility

There are two parameters that you must take care of in your themes and your controllers because they are not mandatory in all website cases:

- **Visibility**
- **Publication date and time**

For example, *publication date and time* won’t be necessary in plain text pages and non-timestampable contents. But we decided to add it directly in `NodesSources` entity to be able to filter and order with this field in the Roadiz back office. This would not be possible if you manually created your own `publishedAt` as a node-type field.

::: warning
Pay attention that *publication date and time* (`publishedAt`) and visibility (`node.visible`) **do not prevent** your node-source from being viewed if you do not explicitly forbid access to its controller. This field is not deeply set into Roadiz security mechanics.

If needed, make sure that your node-type controller checks these two fields and throws a `ResourceNotFoundException` if they’re not satisfied.
:::

```php
class BlogPostController extends MyAwesomeTheme
{
    public function indexAction(
        Request $request,
        Node $node = null,
        TranslationInterface $translation = null
    ) {
        $this->prepareThemeAssignation($node, $translation);

        $now = new DateTime("now");
        if (!$nodeSource->getNode()->isVisible() ||
            $nodeSource->getPublishedAt() < $now) {
            throw new ResourceNotFoundException();
        }

        return $this->render(
            'types/blogpost.html.twig',
            $this->assignation
        );
    }
}
```

## Publication Workflow

Each Node state is handled by a *Workflow* to switch between the following five states:

### States

- `NodeStatus::DRAFT`
- `NodeStatus::PENDING`
- `NodeStatus::PUBLISHED`
- `NodeStatus::ARCHIVED`
- `NodeStatus::DELETED`

### Transitions

- review
- reject
- publish
- archive
- unarchive
- delete
- undelete

You cannot change a Node status directly using its *setter*. You must use the Roadiz main *registry* to perform transitions. This prevents unwanted behaviors and allows tracking changes with events and guards:

```php
$nodeWorkflow = $this->workflowRegistry->get($node);
if ($nodeWorkflow->can($node, 'publish')) {
    $nodeWorkflow->apply($node, 'publish');
}
```

## Generating Paths and URLs

You can use `generateUrl()` in your controllers to get a node-source’s path or URL. In your Twig template, you can use the `path` method as described in the Twig section.

```php
use Symfony\Cmf\Component\Routing\RouteObjectInterface;

class BlogPostController extends MyAwesomeTheme
{
    public function indexAction(
        Request $request,
        Node $node = null,
        TranslationInterface $translation = null
    ) {
        $this->prepareThemeAssignation($node, $translation);

        // Generate a path for the current node-source
        $path = $this->generateUrl(
            RouteObjectInterface::OBJECT_BASED_ROUTE_NAME,
            [RouteObjectInterface::ROUTE_OBJECT => $this->nodeSource]
        );
    }
}
```


## Overriding default node-source path generation {#override_default_path}

You can override default node-source path generation in order to use`path()` method in your *Twig* templates but with a custom logic.
For example, you have a `Link` node-type which purpose only is to link to an other node in your website.
When you call *path* or *URL* generation on it, you should prefer getting its linked node path, so you can listen to `RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesPathGeneratingEvent:class` 
event and stop propagation to return your linked node path instead of your *link* node path.

```php
use GeneratedNodeSources\NSLink;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesPathGeneratingEvent;

class LinkPathGeneratingEventListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            NodesSourcesPathGeneratingEvent::class => ['onLinkPathGeneration']
        ];
    }

    /**
     * @param NodesSourcesPathGeneratingEvent $event
     * @param string                          $eventName
     * @param EventDispatcherInterface        $dispatcher
     */
    public function onLinkPathGeneration(
        NodesSourcesPathGeneratingEvent $event,
        $eventName,
        EventDispatcherInterface $dispatcher
    ) {
        $nodeSource = $event->getNodeSource();

        if ($nodeSource instanceof NSLink) {
            if (filter_var($nodeSource->getExternalUrl(), FILTER_VALIDATE_URL)) {
                /*
                 * If editor linked to an external link
                 */
                $event->stopPropagation();
                $event->setComplete(true);
                $event->setContainsScheme(true); // Tells router not to prepend protocol and host to current URL
                $event->setPath($nodeSource->getExternalUrl());
            } elseif (count($nodeSource->getNodeReferenceSources()) > 0 &&
                null !== $linkedSource = $nodeSource->getNodeReferenceSources()[0]) {
                /*
                 * If editor linked to an internal page through a node reference
                 */
                /** @var FilterNodeSourcePathEvent $subEvent */
                $subEvent = clone $event;
                $subEvent->setNodeSource($linkedSource);
                /*
                 * Dispatch a path generation again for linked node-source.
                 */
                $dispatcher->dispatch($subEvent);
                /*
                 * Fill main event with sub-event data
                 */
                $event->setPath($subEvent->getPath());
                $event->setComplete($subEvent->isComplete());
                $event->setParameters($subEvent->getParameters());
                $event->setContainsScheme($subEvent->containsScheme());
                // Stop propagation AFTER sub-event was dispatched not to prevent it to perform.
                $event->stopPropagation();
            }
        }
    }
}
```
