import { type Args } from '../../../../stories/RzColorInput.stories'

export function rzColorInputRenderer(args: Args) {
    const node = document.createElement('rz-color-input')

    const color = document.createElement('input')
    color.type = 'color'
    color.value = args.value || ''
    node.appendChild(color)

    Object.entries(args.attributes || {}).forEach(([key, value]) => {
        if (value) color.setAttribute(key, String(value))
    })

    const text = document.createElement('input')
    text.type = 'text'
    text.value = args.value || ''
    text.id = args.id || 'color-input-id'
    if (args.textPattern) text.pattern = args.textPattern
    if (args.textMaxLength) text.maxLength = args.textMaxLength
    if (args.placeholder) text.placeholder = args.placeholder
    node.appendChild(text)

    return node
}
