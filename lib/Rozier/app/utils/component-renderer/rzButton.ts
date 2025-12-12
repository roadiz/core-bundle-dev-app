import { type RzElement, rzElement } from '~/utils/component-renderer/rzElement'
import { rzIconRenderer } from '~/utils/component-renderer/rzIcon'

const COMPONENT_CLASS_NAME = 'rz-button'

export const EMPHASIS = ['tertiary', 'secondary', 'primary'] as const
export const SIZES = ['xs', 'sm', 'md', 'lg'] as const
export const COLORS = ['success', 'danger'] as const

export type RzButtonData = RzElement & {
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

export function rzButtonRenderer(data: RzButtonData) {
    const root = rzElement({
        ...data,
        is: 'rz-button',
        tag: 'button',
    })
    root.classList.add(COMPONENT_CLASS_NAME)

    if (data.emphasis) {
        root.classList.add(`${COMPONENT_CLASS_NAME}--${data.emphasis}`)
    }
    if (data.size) {
        root.classList.add(`${COMPONENT_CLASS_NAME}--${data.size}`)
    }
    if (data.disabled) {
        root.classList.add(`${COMPONENT_CLASS_NAME}--disabled`)
        // same style result as class above
        // root.setAttribute('disabled', 'true')
    }
    if (data.selected) {
        root.classList.add(`${COMPONENT_CLASS_NAME}--selected`)
    }
    if (data.onDark) {
        root.classList.add(`${COMPONENT_CLASS_NAME}--on-dark`)
    }
    if (data.color) {
        root.classList.add(`${COMPONENT_CLASS_NAME}--${data.color}`)
    }
    if (data.hasPill) {
        root.classList.add(`${COMPONENT_CLASS_NAME}--pill`)
    }

    if (data.label) {
        const labelNode = rzIconRenderer({
            tag: 'span',
            class: `${COMPONENT_CLASS_NAME}__label`,
            innerHTML: data.label,
        })
        root.appendChild(labelNode)
    }

    if (data.iconClass) {
        const iconNode = rzIconRenderer({
            class: `${COMPONENT_CLASS_NAME}__icon ${data.iconClass}`,
        })
        root.appendChild(iconNode)
    }

    return root
}
