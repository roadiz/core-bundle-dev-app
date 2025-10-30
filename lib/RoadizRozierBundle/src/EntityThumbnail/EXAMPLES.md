# EntityThumbnail Component - Usage Examples

## Example 1: User List (Before vs After)

### Before (Synchronous - blocks page render)
```twig
{# templates/users/list.html.twig #}
<table class="user-list">
    {% for user in users %}
        <tr>
            <td>
                {% if user.email %}
                    <img src="https://www.gravatar.com/avatar/{{ user.email|lower|trim|md5 }}?s=64&d=mp&r=g" 
                         alt="{{ user.username }}" 
                         class="user-avatar">
                {% else %}
                    <div class="user-avatar-placeholder">?</div>
                {% endif %}
            </td>
            <td>{{ user.username }}</td>
            <td>{{ user.email }}</td>
        </tr>
    {% endfor %}
</table>
```

### After (Asynchronous - non-blocking)
```twig
{# templates/users/list.html.twig #}
<table class="user-list">
    {% for user in users %}
        <tr>
            <td>
                <rz-entity-thumbnail 
                    entity-class="{{ user|class }}" 
                    entity-id="{{ user.id }}"
                    size="medium"
                ></rz-entity-thumbnail>
            </td>
            <td>{{ user.username }}</td>
            <td>{{ user.email }}</td>
        </tr>
    {% endfor %}
</table>
```

## Example 2: Node Tree Thumbnails

### Before
```twig
{# Complex logic to get first document from node source #}
{% set firstDocument = null %}
{% if nodeSource.documents|length > 0 %}
    {% set firstDocument = nodeSource.documents|first %}
    {% if firstDocument.isImage %}
        <img src="{{ firstDocument|url({width: 64, height: 64, crop: '1:1'}) }}" 
             alt="{{ nodeSource.title }}">
    {% endif %}
{% endif %}
```

### After
```twig
{# Simple, declarative approach #}
<rz-entity-thumbnail 
    entity-class="{{ nodeSource|class }}" 
    entity-id="{{ nodeSource.id }}"
    size="medium"
></rz-entity-thumbnail>
```

## Example 3: Document Gallery

### Before (Server-side rendering)
```twig
{# All thumbnails rendered immediately - high memory usage #}
<div class="document-gallery">
    {% for document in documents %}
        <div class="gallery-item">
            {% if document.isImage %}
                <img src="{{ document|url({width: 250, crop: '5:4', quality: 60}) }}" 
                     alt="{{ document.filename }}">
            {% else %}
                <div class="placeholder">{{ document.mimeType }}</div>
            {% endif %}
        </div>
    {% endfor %}
</div>
```

### After (Lazy loading from browser)
```twig
{# Thumbnails load asynchronously as they appear #}
<div class="document-gallery">
    {% for document in documents %}
        <div class="gallery-item">
            <rz-entity-thumbnail 
                entity-class="{{ document|class }}" 
                entity-id="{{ document.id }}"
                size="large"
            ></rz-entity-thumbnail>
        </div>
    {% endfor %}
</div>
```

## Example 4: Dashboard Recent Activity

```twig
{# templates/dashboard/index.html.twig #}
<div class="recent-activity">
    {% for log in recentLogs %}
        <div class="activity-item">
            <div class="activity-thumbnail">
                {# Entity type and ID come from log data #}
                <rz-entity-thumbnail 
                    entity-class="{{ log.entityClass }}" 
                    entity-id="{{ log.entityId }}"
                    size="small"
                ></rz-entity-thumbnail>
            </div>
            <div class="activity-details">
                <strong>{{ log.action }}</strong>
                <span>{{ log.timestamp|date('Y-m-d H:i') }}</span>
            </div>
        </div>
    {% endfor %}
</div>
```

## Performance Comparison

### Before (Synchronous)
- Initial page load: **slow** (all thumbnails processed server-side)
- Server memory: **high** (image processing for all thumbnails)
- Time to interactive: **delayed** (blocked by thumbnail generation)
- Network: **large initial payload** (all thumbnail data in HTML)

### After (Asynchronous)
- Initial page load: **fast** (minimal HTML, no image processing)
- Server memory: **low** (only process thumbnails when requested)
- Time to interactive: **immediate** (HTML renders quickly)
- Network: **optimized** (thumbnails load on-demand)

## Migration Guide

1. **Identify thumbnail rendering in templates**
   ```bash
   grep -r "gravatar\|url({.*width.*height" templates/
   ```

2. **Replace with custom element**
   - Extract entity class and ID
   - Choose appropriate size (small/medium/large)
   - Replace img tag with `<rz-entity-thumbnail>`

3. **Test in staging**
   - Verify thumbnails load correctly
   - Check loading states appear
   - Validate tooltips show entity info

4. **Monitor performance**
   - Measure page load time improvement
   - Check server memory usage decrease
   - Verify API endpoint response times
