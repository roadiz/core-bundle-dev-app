/* Think about accessibility during integration, e.g., role="alert" when creating error messages */
export const COLORS = ['neutral', 'success', 'warning', 'error'] as const
export const VARIANTS = ['filled', 'ghost'] as const

export type RzMessageOptions = {
    text: string
    innerHTML?: string
    variant?: (typeof VARIANTS)[number]
    color?: (typeof COLORS)[number]
    iconClass?: string
    textTag?: string
    attributes?: Record<string, string>
}

export function rzMessageRenderer(args: RzMessageOptions) {
    const className = 'rz-message'

    const wrapper = document.createElement('div')

    Object.entries(args.attributes || {}).forEach(([key, value]) => {
        if (typeof value === 'undefined') return
        wrapper.setAttribute(key, String(value))
    })

    wrapper.classList.add(className)

    if (args.color) {
        wrapper.classList.add(`${className}--${args.color}`)
    }
    if (args.variant) {
        wrapper.classList.add(`${className}--${args.variant}`)
    }

    if (args.iconClass) {
        const icon = document.createElement('span')
        icon.classList.add(...args.iconClass.split(' '))
        icon.setAttribute('aria-hidden', 'true')
        wrapper.appendChild(icon)
    }

    // As in Twig: `text` is escaped, only `innerHTML` is inserted as markup
    const text = document.createElement(args.textTag || 'p')
    text.classList.add(`${className}__text`)
    if (args.innerHTML) {
        text.innerHTML = args.innerHTML
    } else {
        text.textContent = args.text
    }
    wrapper.appendChild(text)

    return wrapper
}
