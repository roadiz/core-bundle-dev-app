import type { Args } from '../../../../stories/RzInput.stories'

export const COMPONENT_CLASS = 'rz-input'

type Attributes = {
    [key: string]: unknown
    is?: string
}

export function rzInputRenderer(args: Args, attrs: Attributes = {}) {
    const input = document.createElement('input', {
        is: attrs.is || COMPONENT_CLASS,
    })
    input.setAttribute('is', attrs.is || COMPONENT_CLASS)

    Object.entries(attrs).forEach(([key, value]) => {
        if (value) input.setAttribute(key, String(value))
    })

    input.name = args.name || 'name'
    input.id = args.id || args.name || 'id-needed'
    if (args.type) input.type = args.type
    if (args.placeholder) input.placeholder = args.placeholder
    if (args.required) input.required = args.required

    if (args.value) input.setAttribute('value', String(args.value))

    return input
}
