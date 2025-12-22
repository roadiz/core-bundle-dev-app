import { type RzElement, rzElement } from '~/utils/component-renderer/rzElement'
import { rzIconRenderer } from '~/utils/component-renderer/rzIcon'

const COMPONENT_CLASS_NAME = 'rz-button'

export const EMPHASIS = ['tertiary', 'secondary', 'primary'] as const
export const SIZES = ['xs', 'sm', 'md', 'lg'] as const
export const COLORS = ['success', 'danger'] as const

export type RzButtonOptions = RzElement & {
    label?: string
    emphasis?: (typeof EMPHASIS)[number]
    size?: (typeof SIZES)[number]
    disabled?: boolean
    selected?: boolean
    iconClass?: string
    onDark?: boolean
    color?: (typeof COLORS)[number]
    hasPill?: boolean
}

export function rzButtonRenderer(options: RzButtonOptions) {
    const root = rzElement({
        is: 'rz-button',
        tag: 'button',
        ...options,
    })
    root.classList.add(COMPONENT_CLASS_NAME)

    if (options.emphasis) {
        root.classList.add(`${COMPONENT_CLASS_NAME}--${options.emphasis}`)
    }
    if (options.size) {
        root.classList.add(`${COMPONENT_CLASS_NAME}--${options.size}`)
    }
    if (options.disabled) {
        root.classList.add(`${COMPONENT_CLASS_NAME}--disabled`)
        // same style result as class above
        // root.setAttribute('disabled', 'true')
    }
    if (options.selected) {
        root.classList.add(`${COMPONENT_CLASS_NAME}--selected`)
    }
    if (options.onDark) {
        root.classList.add(`${COMPONENT_CLASS_NAME}--on-dark`)
    }
    if (options.color) {
        root.classList.add(`${COMPONENT_CLASS_NAME}--${options.color}`)
    }
    if (options.hasPill) {
        root.classList.add(`${COMPONENT_CLASS_NAME}--pill`)
    }

    if (options.label) {
        const labelNode = rzElement({
            tag: 'span',
            innerHTML: options.label,
            attributes: { class: `${COMPONENT_CLASS_NAME}__label` },
        })
        root.appendChild(labelNode)
    }

    if (options.iconClass) {
        const iconNode = rzIconRenderer({
            class: `${COMPONENT_CLASS_NAME}__icon ${options.iconClass}`,
        })
        root.appendChild(iconNode)
    }

    return root
}
