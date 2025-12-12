import { rzElement, type RzElement } from '~/utils/component-renderer/rzElement'
import { rzIconRenderer } from './rzIcon'

export const COMPONENT_CLASS_NAME = 'rz-badge'

export const SIZES = ['xs', 'sm', 'md'] as const
export const COLORS = ['information', 'success', 'warning', 'danger'] as const

export type RzBadgeOptions = RzElement & {
    iconClass?: string
    label?: string
    title?: string
    size?: (typeof SIZES)[number]
    color?: (typeof COLORS)[number]
}

export function rzBadgeRenderer(options: RzBadgeOptions) {
    const root = rzElement({
        tag: 'span',
        ...options,
    })
    root.classList.add(COMPONENT_CLASS_NAME)

    if (options.size) {
        root.classList.add(`${COMPONENT_CLASS_NAME}--${options.size}`)
    }
    if (options.color) {
        root.classList.add(`${COMPONENT_CLASS_NAME}--${options.color}`)
    }

    if (options.iconClass) {
        const icon = rzIconRenderer({
            class: `${COMPONENT_CLASS_NAME}__icon ${options.iconClass}`,
        })
        root.appendChild(icon)
    }

    if (options.label) {
        const label = document.createElement('span')
        label.classList.add(`${COMPONENT_CLASS_NAME}__label`)
        label.innerHTML = options.label
        root.appendChild(label)
    }

    return root
}
