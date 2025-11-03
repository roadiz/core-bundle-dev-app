import type { Args } from '../../../../stories/RzMessage.stories'

export function rzMessageRenderer(args: Args) {
    const className = 'rz-message'

    const wrapper = document.createElement('div')
    const classList = [
        className,
        args.color && `${className}--${args.color}`,
    ].filter((c) => c)
    wrapper.classList.add(...classList)

    const text = document.createElement('p')
    text.classList.add(`${className}__text`)
    text.textContent = args.text
    wrapper.appendChild(text)

    return wrapper
}
