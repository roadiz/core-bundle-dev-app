export type Args = {
    displayThumbnail?: boolean
    nodeId?: string
    status?: 'published' | 'draft'
    size?: 'small' | 'medium'
    color?: string
}

const CLASS_NAME = 'rz-node-icon'

export function rzNodeIconRenderer(args: Args) {
    const element = document.createElement('div')
    element.classList.add(CLASS_NAME)

    if (args.size === 'medium') {
        const thumbnail = document.createElement('rz-entity-thumbnail')
        thumbnail.setAttribute(
            'entity-class',
            'RZ\\Roadiz\\CoreBundle\\Entity\\Node',
        )
        thumbnail.setAttribute('entity-id', args.nodeId || '42')
        thumbnail.classList.add(`${CLASS_NAME}__thumbnail`)
        element.appendChild(thumbnail)
    }

    const border = document.createElement('div')
    border.classList.add(
        `${CLASS_NAME}__border`,
        `rz-icon-rz--status-${args.status}-line`,
    )
    element.appendChild(border)

    if (args.status) {
        element.classList.add(`${CLASS_NAME}--${args.status}`)
    }

    if (args.color) {
        element.style.setProperty('--rz-node-icon-color', args.color)
    }

    return element
}
