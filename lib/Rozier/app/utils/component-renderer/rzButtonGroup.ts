import { type RzElement, rzElement } from '~/utils/component-renderer/rzElement'
import {
    rzButtonRenderer,
    type RzButtonOptions,
} from '~/utils/component-renderer/rzButton'

export const COMPONENT_CLASS_NAME = 'rz-button-group'

export const GAPS = ['sm', 'md', 'lg'] as const
export const SIZES = ['xs', 'sm', 'md', 'lg'] as const

export type RzButtonGroupOptions = RzElement & {
    collapsed?: boolean
    gap?: (typeof GAPS)[number]
    size?: (typeof SIZES)[number]
    buttons?: RzButtonOptions[]
}

export function rzButtonGroupRenderer(options: RzButtonGroupOptions) {
    const root = rzElement(options)
    root.classList.add(COMPONENT_CLASS_NAME)

    if (options.size) {
        root.classList.add(`${COMPONENT_CLASS_NAME}--${options.size}`)
    }

    if (options.gap) {
        root.classList.add(`${COMPONENT_CLASS_NAME}--gap-${options.gap}`)
    }

    if (options.collapsed) {
        root.classList.add(`${COMPONENT_CLASS_NAME}--collapsed`)
    }

    options.buttons?.forEach((buttonArgs) => {
        const buttonElement = rzButtonRenderer(buttonArgs)
        root.appendChild(buttonElement)
    })

    return root
}
