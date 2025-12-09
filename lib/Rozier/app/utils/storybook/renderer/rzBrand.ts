import type { Args } from '../../../../stories/RzBrand.stories'

const COMPONENT_CLASS_NAME = 'rz-brand'

export function rzBrandRenderer(args: Args) {
    const wrapper = document.createElement(args.tag || 'div')
    wrapper.classList.add(COMPONENT_CLASS_NAME)

    if (args.color) {
        wrapper.style.setProperty(
            `--${COMPONENT_CLASS_NAME}-background-color`,
            args.color,
        )
    }

    if (args.innerText) {
        wrapper.innerText = args.innerText
    } else if (args.iconClass) {
        const icon = document.createElement('span')
        icon.className = args.iconClass
        wrapper.appendChild(icon)
    }

    return wrapper
}
