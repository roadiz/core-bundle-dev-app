---
title: Tag system
---

# Tag system

Nodes are essentially hierarchical entities. So we created an entity to link nodes between them no matter where/what they are.
Tags are meant as *tag* nodes, we couldn't be more explicit.
But if you didn't understand here is a schema :

![image](./tags.svg){.align-center}

You can see that tags can gather heterogeneous nodes coming from different types (pages and projects).
Tags can be used to display a category-navigation on your theme or to simply tidy your backoffice node database.

Did you notice that `Tags` are related to `Nodes` entities, not `NodesSources`?
We thought that it would be easier to manage that way not to forget to tag a specific node translation.
It means that you won't be able to differentiate tag two `NodesSources`, if you absolutely need to, we encourage you to create two different nodes.

## Translate tags

You will notice that tags work the same way as nodes do.
By default, *tags names* can't contain special characters in order to be used in URLs.
So we created `TagTranslation` entities which stand for Tag's sources:

![image](./tag-translations.svg){.align-center}

In that way you will be able to translate your tags for each available languages and link documents to them.

## Tag hierarchy

In the same way as *Nodes* work, tags can be nested to create *tag groups*.

## Exposing tags in API

When using API Platform data transfer objects, Tags are ready-to-use with translations set-up on `name` and `description` fields:

```json
{
    "@type": "Tag",
    "@id": "/api/tags/6",
    "slug": "event",
    "name": "Event",
    "description": null,
    "color": "#000000",
    "visible": true,
    "documents": [],
    "parent": {
        "@type": "Tag",
        "@id": "/api/tags/3",
        "slug": "type",
        "name": "Type",
        "description": null,
        "color": "#000000",
        "visible": true,
        "documents": [],
        "parent": null
    }
}
```

## Displaying node-source tags with Twig

Tag translations are already set up to track your current locale if you fetched them using `|tags` *Twig* filter.
Simply use `getTranslatedTags()->first()` Tag method to use them in your templates.

```twig
{% set tags = nodeSource|tags %}

<ul>
{% for tag in tags %}
    {% set tagTranslation = tag.translatedTags.first %}
    <li id="{{ tag.tagName }}">{{ tagTranslation.name }}</li>
{% endfor %}
</ul>
```

### Tags translations documents

Documents can be linked to your tag translations.
They will be different for each translation,so make sure to synchronize them manually if you want to use the same document for all translations.

They are available with `getDocuments()` method and will be ordered by **position only**.

Imagine, you want to link a PDF document for each of your tags, you can create a download link as described below:

```twig
{% set tags = nodeSource|tags %}
<ul>
{% for tag in tags %}
    {% set tagTranslation = tag.translatedTags.first %}
    <li id="{{ tag.tagName }}">
        <p>{{ tagTranslation.name }}</p>
        {% if tagTranslation.documents[0] %}
            <a href="{{ tagTranslation.documents[0]|url }}" class="tag-document">{% trans %}download_tag_pdf{% endtrans %}</a>
        {% endif %}
    </li>
{% endfor %}
</ul>
```
