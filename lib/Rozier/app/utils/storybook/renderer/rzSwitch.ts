import type { Args } from '../../../../stories/RzSwitch.stories'

const COMPONENT_CLASS_NAME = 'rz-switch'

export function rzSwitchRenderer(args: Args) {
    const input = document.createElement('input')
    input.setAttribute('type', 'checkbox')
    input.classList.add(COMPONENT_CLASS_NAME)

    Object.entries(args.attributes || {}).forEach(([key, value]) => {
        if (value) input.setAttribute(key, String(value))
    })

    if (args.checked) input.setAttribute('checked', '')

    return input
}
