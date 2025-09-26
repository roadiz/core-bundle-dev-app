# Roadiz events

Roadiz node system implements several events. So you will be able to create and inject your own event subscribers inside *Roadiz* dispatcher.

To understand how the event dispatcher works, you should read the [Symfony documentation at](http://symfony.com/doc/current/components/event_dispatcher/introduction.html) before.

## Nodes events

- `RZ\Roadiz\CoreBundle\Event\Node\NodeCreatedEvent`
- `RZ\Roadiz\CoreBundle\Event\Node\NodeUpdatedEvent`
- `RZ\Roadiz\CoreBundle\Event\Node\NodeDeletedEvent`
- `RZ\Roadiz\CoreBundle\Event\Node\NodeUndeletedEvent`
- `RZ\Roadiz\CoreBundle\Event\Node\NodeDuplicatedEvent`
- `RZ\Roadiz\CoreBundle\Event\Node\NodePathChangedEvent`
- `RZ\Roadiz\CoreBundle\Event\Node\NodeTaggedEvent`: This event is triggered for tag and un-tag action.
- `RZ\Roadiz\CoreBundle\Event\Node\NodeVisibilityChangedEvent`: This event is triggered each time a node becomes visible or unvisible.
- `RZ\Roadiz\CoreBundle\Event\Node\NodeStatusChangedEvent`: This event is triggered each time a node status changes.

Each node event object contains the current `Node` entity. You will get it using `$event->getNode()`.

## NodesSources events

Each `RZ\Roadiz\CoreBundle\Event\NodesSourcesEvents` object contains the current `NodesSources` entity. You will get it using `$event->getNodeSource()`.


- `RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesCreatedEvent`
- `RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesPreUpdatedEvent`: This event is dispatched BEFORE entity manager FLUSHED.
- `RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesUpdatedEvent`: This event is dispatched AFTER entity manager FLUSHED.
- `RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesDeletedEvent`
- `RZ\Roadiz\SolrBundle\Event\NodesSources\NodesSourcesIndexingEvent`: This event type is dispatched during Solr indexation, it will allow you to alter or improve your search-engine index according to your node-source type.
    ::: tip
    Default search-engine Roadiz subscriber is located in SolrBundle: `RZ\Roadiz\SolrBundle\EventListener\SolariumSubscriber`.
    This subscriber is useful to update or delete your *Solr* index documents against your node-source database.
    :::
- `RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesPathGeneratingEvent`: This event type is dispatched when the node-router 
generate a path for your node-source using `path()` Twig method or `$this->urlGenerator->generate()` controller method.
The default subscriber will generate the complete hierarchical path for any node-source using their identifier (available url-alias or node' name).

## Tags events

- `RZ\Roadiz\CoreBundle\Event\Tag\TagCreatedEvent`
- `RZ\Roadiz\CoreBundle\Event\Tag\TagUpdatedEvent`
- `RZ\Roadiz\CoreBundle\Event\Tag\TagDeletedEvent`

Each tag event object contains the current `Tag` entity. You will get it using `$event->getTag()`.

## Folders events

- `RZ\Roadiz\CoreBundle\Event\Folder\FolderCreatedEvent`
- `RZ\Roadiz\CoreBundle\Event\Folder\FolderUpdatedEvent`
- `RZ\Roadiz\CoreBundle\Event\Folder\FolderDeletedEvent`

Each folder event object contains the current `Folder` entity. You will get it using `$event->getFolder()`.

## Translations events

- `RZ\Roadiz\CoreBundle\Event\Translation\TranslationCreatedEvent`
- `RZ\Roadiz\CoreBundle\Event\Translation\TranslationUpdatedEvent`
- `RZ\Roadiz\CoreBundle\Event\Translation\TranslationDeletedEvent`

Each folder event object contains the current `Translation` entity. You will get it using `$event->getTranslation()`.

## UrlAlias events

- `RZ\Roadiz\CoreBundle\Event\UrlAlias\UrlAliasCreatedEvent`
- `RZ\Roadiz\CoreBundle\Event\UrlAlias\UrlAliasUpdatedEvent`
- `RZ\Roadiz\CoreBundle\Event\UrlAlias\UrlAliasDeletedEvent`

Each folder event object contains the current `UrlAlias` entity. You will get it using `$event->getUrlAlias()`.

## Redirections events

- `RZ\Roadiz\CoreBundle\Event\Redirection\PostCreatedRedirectionEvent`
- `RZ\Roadiz\CoreBundle\Event\Redirection\PostDeletedRedirectionEvent`
- `RZ\Roadiz\CoreBundle\Event\Redirection\PostUpdatedRedirectionEvent`

Each redirection event object contains the current `Redirection` entity. You will get it using `$event->getRedirection()`.

## Realms events

- `RZ\Roadiz\CoreBundle\Event\Realm\NodeJoinedRealmEvent`
- `RZ\Roadiz\CoreBundle\Event\Realm\NodeLeftRealmEvent`

Each realm event object contains the current `RealmNode` data transfer object. You will get it using `$event->getRealmNode()`.

## User events

- `RZ\Roadiz\CoreBundle\Event\User\UserCreatedEvent`
- `RZ\Roadiz\CoreBundle\Event\User\UserDeletedEvent`
- `RZ\Roadiz\CoreBundle\Event\User\UserDisabledEvent`
- `RZ\Roadiz\CoreBundle\Event\User\UserEnabledEvent`
- `RZ\Roadiz\CoreBundle\Event\User\UserJoinedGroupEvent`
- `RZ\Roadiz\CoreBundle\Event\User\UserLeavedGroupEvent`
- `RZ\Roadiz\CoreBundle\Event\User\UserPasswordChangedEvent`
- `RZ\Roadiz\CoreBundle\Event\User\UserUpdatedEvent`

Each folder event object contains the current `User` entity. You will get it using `$event->getUser()`.

## Custom Form Answers events

- `RZ\Roadiz\CoreBundle\Event\CustomFormAnswer\CustomFormAnswerSubmittedEvent`: After a custom form answer has been submitted and saved.
