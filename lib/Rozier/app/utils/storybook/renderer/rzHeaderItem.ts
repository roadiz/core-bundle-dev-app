import type { Args } from '../../../../stories/RzHeaderItem.stories'

const COMPONENT_CLASS_NAME = 'rz-header-item'

export function rzHeaderItemRenderer(args: Args) {
    const customElement = args.attributes?.is
    const node = document.createElement(
        args.tag || 'div',
        customElement ? { is: customElement } : undefined,
    )

    if (args.attributes) {
        Object.entries(args.attributes).forEach(([key, value]) => {
            node.setAttribute(key, value)
        })
    }

    const classes = [
        COMPONENT_CLASS_NAME,
        args.variants && `${COMPONENT_CLASS_NAME}--${args.variants}`,
        args.active && `${COMPONENT_CLASS_NAME}--active`,
    ].filter((c) => c) as string[]
    node.classList.add(...classes)

    if (args.iconClass) {
        const icon = document.createElement('span')
        icon.classList.add(`${COMPONENT_CLASS_NAME}__icon`, args.iconClass)
        node.appendChild(icon)
    }

    const label = document.createTextNode(args.label)
    node.appendChild(label)

    if (customElement || args.tag === 'a') {
        const arrowIcon = document.createElement('span')
        const iconName =
            args.tag === 'a'
                ? `rz-icon-ri--arrow-right-s-line`
                : `rz-icon-ri--arrow-down-s-line`

        arrowIcon.classList.add(`${COMPONENT_CLASS_NAME}__arrow`, iconName)
        node.appendChild(arrowIcon)
    }

    return node
}
