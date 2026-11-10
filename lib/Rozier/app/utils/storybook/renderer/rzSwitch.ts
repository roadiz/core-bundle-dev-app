import type { RzInputOptions } from '~/utils/storybook/renderer/rzInput'

export type RzSwitchOptions = RzInputOptions & {
    checked?: boolean
}

const COMPONENT_CLASS_NAME = 'rz-switch'

export function rzSwitchRenderer(args: RzSwitchOptions) {
    const input = document.createElement('input')
    input.setAttribute('type', 'checkbox')
    input.classList.add(COMPONENT_CLASS_NAME)

    Object.entries(args.attributes || {}).forEach(([key, value]) => {
        if (value) input.setAttribute(key, String(value))
    })

    if (args.checked) input.setAttribute('checked', '')
    if (args.id) input.setAttribute('id', args.id)

    return input
}
