import { type RzElement, rzElement } from '~/utils/component-renderer/rzElement'
import {
    rzButtonRenderer,
    type RzButtonData,
} from '~/utils/component-renderer/rzButton'

export const COMPONENT_CLASS_NAME = 'rz-button-group'

export const GAPS = ['sm', 'md', 'lg'] as const
export const SIZES = ['xs', 'sm', 'md', 'lg'] as const

export type RzButtonGroupData = RzElement & {
    collapsed?: boolean
    gap?: (typeof GAPS)[number]
    size?: (typeof SIZES)[number]
    buttons?: RzButtonData[]
}

export function rzButtonGroupRenderer(data: RzButtonGroupData) {
    const root = rzElement(data)
    root.classList.add(COMPONENT_CLASS_NAME)

    if (data.size) {
        root.classList.add(`${COMPONENT_CLASS_NAME}--${data.size}`)
    }

    if (data.gap) {
        root.classList.add(`${COMPONENT_CLASS_NAME}--gap-${data.gap}`)
    }

    if (data.collapsed) {
        root.classList.add(`${COMPONENT_CLASS_NAME}--collapsed`)
    }

    data.buttons?.forEach((buttonArgs) => {
        const buttonElement = rzButtonRenderer(buttonArgs)
        root.appendChild(buttonElement)
    })

    return root
}
