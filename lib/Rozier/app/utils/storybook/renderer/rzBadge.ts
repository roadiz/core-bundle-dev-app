import type { BadgeArgs } from '../../../../stories/rzBadge.stories'

export const COMPONENT_CLASS_NAME = 'rz-badge'

export function rzBadgeRenderer(args: BadgeArgs) {
    const node = document.createElement('div')
    const classes = [
        COMPONENT_CLASS_NAME,
        args.size && `${COMPONENT_CLASS_NAME}--size-${args.size}`,
        args.color && `${COMPONENT_CLASS_NAME}--${args.color}`,
    ].filter((c) => c)
    node.classList.add(...classes)

    if (args.iconClass) {
        const icon = document.createElement('span')
        icon.classList.add(`${COMPONENT_CLASS_NAME}__icon`, args.iconClass)
        node.appendChild(icon)
    }

    if (args.label) {
        const label = document.createElement('span')
        label.classList.add(`${COMPONENT_CLASS_NAME}__label`)
        label.textContent = args.label
        node.appendChild(label)
    }

    return node
}
