# EntityThumbnail Custom Element

## Overview

The `rz-entity-thumbnail` custom element provides an asynchronous way to display thumbnails for various Roadiz entities (User, Document, NodesSources, etc.) without blocking the initial page render. It fetches thumbnail information from a backend API endpoint on mount.

## Features

- **Chain of Responsibility Pattern**: Extensible provider system for different entity types
- **Async Loading**: Thumbnails are fetched asynchronously, improving page load performance
- **Loading States**: Visual feedback during fetch (spinner, error, empty states)
- **Tooltip Support**: Automatically displays entity title on hover
- **Size Variants**: Small (32x32), Medium (64x64), Large (128x128)
- **Gravatar Integration**: Automatic Gravatar support for User entities
- **Document Support**: Image thumbnails for Document entities
- **NodesSources Support**: First document image for NodesSources

## Usage

### Basic HTML Usage

**Note**: Always use fully qualified class names (FQCN) for the `entity-class` attribute.

```html
<!-- User thumbnail -->
<rz-entity-thumbnail entity-class="RZ\Roadiz\CoreBundle\Entity\User" entity-id="1"></rz-entity-thumbnail>

<!-- Document thumbnail -->
<rz-entity-thumbnail entity-class="RZ\Roadiz\CoreBundle\Entity\Document" entity-id="42"></rz-entity-thumbnail>

<!-- NodesSources thumbnail -->
<rz-entity-thumbnail entity-class="RZ\Roadiz\CoreBundle\Entity\NodesSources" entity-id="123"></rz-entity-thumbnail>
```

### Size Variants

```html
<!-- Small (32x32) -->
<rz-entity-thumbnail entity-class="RZ\Roadiz\CoreBundle\Entity\User" entity-id="1" size="small"></rz-entity-thumbnail>

<!-- Medium (64x64) - default -->
<rz-entity-thumbnail entity-class="RZ\Roadiz\CoreBundle\Entity\User" entity-id="1" size="medium"></rz-entity-thumbnail>

<!-- Large (128x128) -->
<rz-entity-thumbnail entity-class="RZ\Roadiz\CoreBundle\Entity\User" entity-id="1" size="large"></rz-entity-thumbnail>
```

### Twig Template Usage

```twig
{# User thumbnail #}
<rz-entity-thumbnail 
    entity-class="{{ user|class }}" 
    entity-id="{{ user.id }}"
></rz-entity-thumbnail>

{# Document thumbnail with size #}
<rz-entity-thumbnail 
    entity-class="{{ document|class }}" 
    entity-id="{{ document.id }}"
    size="large"
></rz-entity-thumbnail>

{# NodesSources thumbnail #}
<rz-entity-thumbnail 
    entity-class="{{ nodeSource|class }}" 
    entity-id="{{ nodeSource.id }}"
></rz-entity-thumbnail>
```

## API Endpoint

The custom element fetches data from:
```
GET /rz-admin/ajax/entity-thumbnail?class={FQCN}&id={entityId}
```

**Note**: The `class` parameter must be a fully qualified class name (FQCN).

### Response Format

```json
{
    "url": "https://example.com/path/to/thumbnail.jpg",
    "alt": "Alternative text",
    "title": "Tooltip text"
}
```

## Extending with Custom Providers

To add support for new entity types, create a provider extending `AbstractEntityThumbnailProvider`:

```php
<?php

namespace App\EntityThumbnail\Provider;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\RozierBundle\EntityThumbnail\AbstractEntityThumbnailProvider;

final class CustomEntityThumbnailProvider extends AbstractEntityThumbnailProvider
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
    ) {
    }

    public function supports(string $entityClass, int|string $identifier): bool
    {
        return $this->isClassSupported($entityClass, CustomEntity::class);
    }

    public function getThumbnail(string $entityClass, int|string $identifier): ?array
    {
        // Provider is responsible for fetching the entity
        $repository = $this->managerRegistry->getRepository($entityClass);
        $entity = $repository->find($identifier);
        
        if (!$entity instanceof CustomEntity) {
            return null;
        }
        
        // Your logic here
        $url = // ... generate thumbnail URL
        $alt = $entity->getName();
        $title = $entity->getTitle();
        
        return $this->createResponse($url, $alt, $title);
    }
}
```

**Note:** Providers are responsible for fetching entities from the database. This allows you to support different identifier types (e.g., ID, email, slug) for the same entity class.

Tag the provider in your services configuration:

```yaml
services:
    App\EntityThumbnail\Provider\CustomEntityThumbnailProvider:
        tags: ['roadiz.entity_thumbnail_provider']
```

## CSS Customization

The component uses CSS custom properties for theming:

```css
rz-entity-thumbnail {
    /* Override border radius */
    --global-radius: 8px;
    
    /* Override colors */
    --global-background-color: #f5f5f5;
    --global-border-color: #ddd;
    --global-primary-color: #007bff;
}
```

## States

The component renders different states:

1. **Loading**: Displays a spinner while fetching data
2. **Success**: Shows the thumbnail image
3. **Empty**: Shows a placeholder when no image is available
4. **Error**: Shows an error indicator with tooltip

## Benefits

- **Performance**: Offloads Twig rendering by fetching thumbnails asynchronously
- **Scalability**: Reduces initial page load time for pages with many thumbnails
- **User Experience**: Progressive enhancement with loading indicators
- **Maintainability**: Centralized thumbnail logic via providers
- **Flexibility**: Easy to add support for new entity types

## Browser Support

Works in all modern browsers with native Web Components support.
