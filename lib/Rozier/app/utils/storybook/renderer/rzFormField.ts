import type { Args } from '../../../../stories/RzFormField.stories'
import { rzInputRenderer } from './rzInput'
import { rzMessageRenderer } from './rzMessage'

const COMPONENT_CLASS_NAME = 'rz-form-field'

export function rzFormFieldRenderer(args: Args) {
    const wrapper = document.createElement('div')
    const wrapperClasses = [
        COMPONENT_CLASS_NAME,
        `${COMPONENT_CLASS_NAME}--type-${args.type}`,
        args.required && `${COMPONENT_CLASS_NAME}--required`,
        args.inline && `${COMPONENT_CLASS_NAME}--inline`,
    ].filter((c) => c) as string[]
    wrapper.classList.add(...wrapperClasses)

    const label = document.createElement('label')
    label.classList.add(`${COMPONENT_CLASS_NAME}__label`)
    label.textContent = args.label
    label.setAttribute('for', args.name)
    wrapper.appendChild(label)

    if (args.description) {
        const description = document.createElement('label')
        description.classList.add(`${COMPONENT_CLASS_NAME}__description`)
        description.setAttribute('for', args.name)
        description.textContent = args.description
        wrapper.appendChild(description)
    }

    const input = rzInputRenderer({
        ...args,
        name: args.name || 'input',
        placeholder: 'Placeholder',
    })
    input.classList.add(`${COMPONENT_CLASS_NAME}__input`, 'rz-form-input')
    if (args.error) input.classList.add('rz-input--error')
    if (args.required) input.setAttribute('required', 'true')
    wrapper.appendChild(input)

    if (args.help) {
        const node = rzMessageRenderer({ text: args.help })
        node.classList.add(`${COMPONENT_CLASS_NAME}__message`)
        node.setAttribute('for', args.name)
        wrapper.appendChild(node)
    }

    if (args.error) {
        const node = rzMessageRenderer({ text: args.error, type: 'error' })
        node.classList.add(`${COMPONENT_CLASS_NAME}__message`)
        node.setAttribute('for', args.name)
        wrapper.appendChild(node)
    }

    return wrapper
}
