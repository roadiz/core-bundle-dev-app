export const SIZES = ['small', 'medium', 'large'] as const

export type RzEntityThumbnailOptions = {
    entityClass: string
    entityId: string
    size?: (typeof SIZES)[number]
}

export function rzEntityThumbnailRenderer(args: RzEntityThumbnailOptions) {
    const thumbnail = document.createElement('rz-entity-thumbnail')

    thumbnail.setAttribute('entity-class', args.entityClass)
    thumbnail.setAttribute('entity-id', args.entityId)

    if (args.size) {
        thumbnail.classList.add(`rz-entity-thumbnail--${args.size}`)
    }

    return thumbnail
}
