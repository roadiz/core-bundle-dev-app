import type { Args } from '../../../../stories/RzInput.stories'

export const COMPONENT_CLASS = 'rz-input'
export const COMPONENT_CLASS_NAME = 'rz-input'

export function rzInputRenderer(
    args: Args,
    attrs: Record<string, unknown> = {},
) {
    const input = document.createElement('input', { is: COMPONENT_CLASS })
    input.setAttribute('is', COMPONENT_CLASS)
    input.classList.add(args.className || COMPONENT_CLASS_NAME)

    Object.entries(attrs).forEach(([key, value]) => {
        if (value) input.setAttribute(key, String(value))
    })

    if (args.name) {
        input.name = args.name
        input.id = args.id || args.name
    }
    if (args.type) input.type = args.type
    if (args.placeholder) input.placeholder = args.placeholder
    if (args.required) input.required = args.required

    if (args.value) input.setAttribute('value', String(args.value))

    return input
}
