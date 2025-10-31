import type { Args } from '../../../../stories/RzMessage.stories'

export function rzMessageRenderer(args: Args) {
    const className = 'rz-message'

    const wrapper = document.createElement('div')
    const classList = [
        className,
        args.type ? `${className}--type-${args.type}` : '',
    ].filter((c) => c)
    wrapper.classList.add(...classList)

    if (args.type === 'error') {
        /* If needed, think about accessibility during integration */
        wrapper.setAttribute('role', 'alert')
    }

    const text = document.createElement('p')
    text.classList.add('text-form-supporting-text')
    text.textContent = args.text
    wrapper.appendChild(text)

    return wrapper
}
