import type { Args } from '../../../../stories/RzMessage.stories'

export function rzMessageRenderer(args: Args) {
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
