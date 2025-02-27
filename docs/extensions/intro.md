---
title: Events
prev: false
---

# Extensions Introduction

Roadiz node system implements several events. So you will be able to create and inject your own event subscribers inside *Roadiz* dispatcher.

To understand how the event dispatcher works, you should read the [Symfony documentation at](http://symfony.com/doc/current/components/event_dispatcher/introduction.html) before.

## Nodes events

- `RZ\Roadiz\CoreBundle\Event\Node\NodeCreatedEvent::class`
- `RZ\Roadiz\CoreBundle\Event\Node\NodeUpdatedEvent::class`
- `RZ\Roadiz\CoreBundle\Event\Node\NodeDeletedEvent::class`
- `RZ\Roadiz\CoreBundle\Event\Node\NodeUndeletedEvent::class`
- `RZ\Roadiz\CoreBundle\Event\Node\NodeDuplicatedEvent::class`
- `RZ\Roadiz\CoreBundle\Event\Node\NodePathChangedEvent::class`
- `RZ\Roadiz\CoreBundle\Event\Node\NodeTaggedEvent::class`: This event is triggered for tag and un-tag action.
- `RZ\Roadiz\CoreBundle\Event\Node\NodeVisibilityChangedEvent::class`: This event is triggered each time a node becomes visible or unvisible.
- `RZ\Roadiz\CoreBundle\Event\Node\NodeStatusChangedEvent::class`: This event is triggered each time a node status changes.

Each node event object contains the current `Node` entity. You will get it using `$event->getNode()`.

## NodesSources events

`RZ\Roadiz\CoreBundle\Event\NodesSourcesEvents`

- `RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesCreatedEvent::class`
- `RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesPreUpdatedEvent::class`: This event is dispatched BEFORE entity manager FLUSHED.
- `RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesUpdatedEvent::class`: This event is dispatched AFTER entity manager FLUSHED.
- `RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesDeletedEvent::class`

Each node-source event object contains the current `NodesSources` entity. You will get it using `$event->getNodeSource()`.

`RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesIndexingEvent::class`:
> This event type is dispatched during Solr indexation.
> Your event will be `\RZ\Roadiz\CoreBundle\Event\FilterSolariumNodeSourceEvent` and it will allow you to alter or improve your Solr index according to your node-source type.

::: tip
You will find a simple subscriber example in Roadiz back-office theme which is called `Themes\Rozier\Events\SolariumSubscriber`.

This subscriber is useful to update or delete your *Solr* index documents against your node-source database.
:::

`RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesPathGeneratingEvent::class` :
> This event type is dispatched when the node-router generate a path for your node-source using `path()` Twig method or `$this->urlGenerator->generate()` controller method.
> 
> The default subscriber will generate the complete hierarchical path for any node-source using their identifier (available url-alias or node' name).

# Tags events

- `RZ\Roadiz\CoreBundle\Event\Tag\TagCreatedEvent::class`
- `RZ\Roadiz\CoreBundle\Event\Tag\TagUpdatedEvent::class`
- `RZ\Roadiz\CoreBundle\Event\Tag\TagDeletedEvent::class`

Each tag event object contains the current `Tag` entity. You will get it using `$event->getTag()`.

# Folders events

- `RZ\Roadiz\CoreBundle\Event\Folder\FolderCreatedEvent::class`
- `RZ\Roadiz\CoreBundle\Event\Folder\FolderUpdatedEvent::class`
- `RZ\Roadiz\CoreBundle\Event\Folder\FolderDeletedEvent::class`

Each folder event object contains the current `Folder` entity. You will get it using `$event->getFolder()`.

# Translations events

- `RZ\Roadiz\CoreBundle\Event\Translation\TranslationCreatedEvent::class`
- `RZ\Roadiz\CoreBundle\Event\Translation\TranslationUpdatedEvent::class`
- `RZ\Roadiz\CoreBundle\Event\Translation\TranslationDeletedEvent::class`

Each folder event object contains the current `Translation` entity. You will get it using `$event->getTranslation()`.

# UrlAlias events

- `RZ\Roadiz\CoreBundle\Event\UrlAlias\UrlAliasCreatedEvent::class`
- `RZ\Roadiz\CoreBundle\Event\UrlAlias\UrlAliasUpdatedEvent::class`
- `RZ\Roadiz\CoreBundle\Event\UrlAlias\UrlAliasDeletedEvent::class`

Each folder event object contains the current `UrlAlias` entity. You will get it using `$event->getUrlAlias()`.

# User events

- `RZ\Roadiz\CoreBundle\Event\User\UserCreatedEvent::class`
- `RZ\Roadiz\CoreBundle\Event\User\UserUpdatedEvent::class`
- `RZ\Roadiz\CoreBundle\Event\User\UserDeletedEvent::class`
- `RZ\Roadiz\CoreBundle\Event\User\UserDisabledEvent::class`
- `RZ\Roadiz\CoreBundle\Event\User\UserEnabledEvent::class`
- `RZ\Roadiz\CoreBundle\Event\User\UserPasswordChangedEvent::class`

Each folder event object contains the current `User` entity. You will get it using `$event->getUser()`.
