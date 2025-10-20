import { EMPHASIS, SIZES } from './constants'

export type ButtonArgs = {
    label: string
    emphasis: (typeof EMPHASIS)[number]
    size: (typeof SIZES)[number]
    disabled: boolean
    iconClass: string
    onDark: boolean
    additionalClasses: string
}
