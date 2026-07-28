import type { Args } from '../../../../stories/RzEntityThumbnail.stories'

export function rzEntityThumbnailRenderer(args: Args) {
    const thumbnail = document.createElement('rz-entity-thumbnail')

    thumbnail.setAttribute('entity-class', args.entityClass)
    thumbnail.setAttribute('entity-id', args.entityId)

    if (args.size) {
        thumbnail.classList.add(`rz-entity-thumbnail--${args.size}`)
    }

    return thumbnail
}
