import { rzElement, type RzElement } from '~/utils/component-renderer/rzElement'

export const COMPONENT_CLASS_NAME = 'rz-badge'

export const SIZES = ['xs', 'sm', 'md'] as const
export const COLORS = ['information', 'success', 'warning', 'danger'] as const

export type RzBadgeData = RzElement & {
    iconClass?: string
    label?: string
    title?: string
    size?: (typeof SIZES)[number]
    color?: (typeof COLORS)[number]
}

export function rzBadgeRenderer(data: RzBadgeData) {
    const root = rzElement(data)
    root.classList.add(COMPONENT_CLASS_NAME)

    if (data.size) {
        root.classList.add(`${COMPONENT_CLASS_NAME}--${data.size}`)
    }
    if (data.color) {
        root.classList.add(`${COMPONENT_CLASS_NAME}--${data.color}`)
    }

    if (data.iconClass) {
        const icon = document.createElement('span')
        icon.classList.add(`${COMPONENT_CLASS_NAME}__icon`, data.iconClass)
        root.appendChild(icon)
    }

    if (data.label) {
        const label = document.createElement('span')
        label.classList.add(`${COMPONENT_CLASS_NAME}__label`)
        label.innerHTML = data.label
        root.appendChild(label)
    }

    return root
}
