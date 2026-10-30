export type RzBrandOptions = {
    tag?: string
    innerText?: string
    iconClass?: string
    color?: string
    attributes?: Record<string, string>
}

const COMPONENT_CLASS_NAME = 'rz-brand'

export function rzBrandRenderer(args: RzBrandOptions) {
    const wrapper = document.createElement(args.tag || 'div')
    wrapper.classList.add(COMPONENT_CLASS_NAME)

    const attributesEntries = Object.entries(args.attributes || {})
    if (attributesEntries.length) {
        attributesEntries.forEach(([key, value]) => {
            wrapper.setAttribute(key, value)
        })
    }

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
