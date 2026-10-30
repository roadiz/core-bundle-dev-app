import type { INPUT_TYPES } from '~/custom-elements/RzInput'

export type RzInputOptions = {
    type: (typeof INPUT_TYPES)[number]
    name: string
    id: string
    placeholder?: string
    value?: string
    required?: boolean
    className?: string
    attributes?: { [key: string]: string }
}

export const COMPONENT_CLASS = 'rz-input'

export function rzInputRenderer(args: RzInputOptions) {
    const input = document.createElement('input', {
        is: args.attributes?.is || COMPONENT_CLASS || '',
    })
    input.setAttribute('is', args.attributes?.is || COMPONENT_CLASS)

    Object.entries(args.attributes || {}).forEach(([key, value]) => {
        if (value) input.setAttribute(key, String(value))
    })

    input.name = args.name || 'name'
    input.id = args.id || args.name || 'id-needed'

    if (args.className) input.classList.add(args.className)
    if (args.type) input.type = args.type
    if (args.placeholder) input.placeholder = args.placeholder
    if (args.required) input.required = args.required

    if (args.value) input.setAttribute('value', String(args.value))

    return input
}
