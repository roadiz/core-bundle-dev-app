import type { ItemArgs } from './types'

export const COMPONENT_CLASS = 'rz-workspace-item'

export function iconRenderer(iconClass: string) {
    if (!iconClass) return undefined
    const icon = document.createElement('span')
    icon.classList.add(`${COMPONENT_CLASS}__icon`, iconClass)

    return icon
}

export function itemRenderer(
    args: ItemArgs,
    createElementOptions?: ElementCreationOptions,
) {
    const node = document.createElement(args.tag, createElementOptions)
    if (createElementOptions?.is) {
        node.setAttribute('is', createElementOptions.is)
    }
    const variantClass = args.variants
        ? `${COMPONENT_CLASS}--${args.variants}`
        : ''
    const activeClass = args.active ? `${COMPONENT_CLASS}--active` : ''
    const classes = [COMPONENT_CLASS, variantClass, activeClass].filter(
        (c) => c,
    )
    node.classList.add(...classes)

    const icon = iconRenderer(args.iconClass)
    if (icon) {
        node.appendChild(icon)
    }

    const label = document.createTextNode(args.label)
    node.appendChild(label)

    return node
}

export function arrowIconRenderer(direction: 'down' | 'right' = 'down') {
    const dropdownIcon = document.createElement('span')
    dropdownIcon.classList.add(
        `${COMPONENT_CLASS}__arrow`,
        `rz-icon-ri--arrow-${direction}-s-line`,
    )

    return dropdownIcon
}
