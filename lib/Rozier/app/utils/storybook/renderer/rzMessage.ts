/* Think about accessibility during integration, e.g., role="alert" when creating error messages */
export const COLORS = ['neutral', 'error'] as const
export const VARIANTS = ['filled', 'ghost'] as const

export type RzMessageOptions = {
    text: string
    innerHTML?: string
    variant?: (typeof VARIANTS)[number]
    color?: (typeof COLORS)[number]
}

export function rzMessageRenderer(args: RzMessageOptions) {
    const className = 'rz-message'

    const wrapper = document.createElement('div')
    wrapper.classList.add(className)

    if (args.color) {
        wrapper.classList.add(`${className}--${args.color}`)
    }
    if (args.variant) {
        wrapper.classList.add(`${className}--${args.variant}`)
    }

    const text = document.createElement('p')
    text.classList.add(`${className}__text`)
    text.innerHTML = args.innerHTML || args.text
    wrapper.appendChild(text)

    return wrapper
}
