# Roadiz Document base system

![Run test status](https://github.com/roadiz/documents/actions/workflows/run-test.yml/badge.svg?branch=develop)

## HTML templates

You can override and inherit from document rendering templates by creating them in your theme at the same
path inside your `views/` folder.

### VueJS and \<noscript\>

You may need to override `<noscript>` block to add `inline-template` attribute :

```twig
{% extends "@Documents/documents/image.html.twig" %}

{% block noscript_attributes %} inline-template{% endblock %}
```

Do not forget to add a leading space before your attributes.
