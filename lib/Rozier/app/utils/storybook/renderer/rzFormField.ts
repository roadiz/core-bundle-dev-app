import type { Args } from '../../../../stories/RzFormField.stories'
import { rzButtonGroupRenderer } from './rzButtonGroup'
import { rzInputRenderer } from './rzInput'
import { rzMessageRenderer } from './rzMessage'
import { rzColorInputRenderer } from './rzColorInput'
import { rzBadgeRenderer } from './rzBadge'
import { rzSwitchRenderer } from './rzSwitch'

const COMPONENT_CLASS_NAME = 'rz-form-field'

export function rzFormFieldRenderer(args: Args) {
    const wrapper = document.createElement('div')
    const inputType = args.input?.type || args.type

    const wrapperClasses = [
        COMPONENT_CLASS_NAME,
        args.required && `${COMPONENT_CLASS_NAME}--required`,
        args.horizontal && `${COMPONENT_CLASS_NAME}--horizontal`,
        args.error && `${COMPONENT_CLASS_NAME}--error`,
    ].filter((c) => c) as string[]
    wrapper.classList.add(...wrapperClasses)

    const head = document.createElement('div')
    head.classList.add(`${COMPONENT_CLASS_NAME}__head`)
    wrapper.appendChild(head)

    if (args.iconClass) {
        const icon = document.createElement('span')
        icon.classList.add(
            `${COMPONENT_CLASS_NAME}__head__icon`,
            args.iconClass,
        )
        head.appendChild(icon)
    }

    const label = document.createElement('label')
    label.classList.add(`${COMPONENT_CLASS_NAME}__head__label`)
    label.textContent = args.label
    label.setAttribute('for', args.input?.id)
    head.appendChild(label)

    if (args.badge) {
        const badge = rzBadgeRenderer({
            ...args.badge,
            size: args.badge.size || 'xs',
        })
        badge.setAttribute('title', args.badge.title || 'Badge title')
        badge.classList.add(`${COMPONENT_CLASS_NAME}__head__badge`)
        head.appendChild(badge)
    }

    if (args.buttonGroup) {
        const buttonGroup = rzButtonGroupRenderer(args.buttonGroup)
        buttonGroup.classList.add(`${COMPONENT_CLASS_NAME}__head__button-group`)
        head.appendChild(buttonGroup)
    }

    let descriptionId: string | undefined = undefined
    if (args.description) {
        descriptionId = `${args.input?.name}-description-${Date.now()}`
        const description = document.createElement('p')
        description.classList.add(`${COMPONENT_CLASS_NAME}__description`)
        description.textContent = args.description
        description.id = descriptionId
        wrapper.appendChild(description)
    }

    if (args.input) {
        const renderer = args.input.className?.includes('rz-switch')
            ? rzSwitchRenderer
            : args.input?.type === 'color'
              ? rzColorInputRenderer
              : rzInputRenderer

        const input = renderer({
            ...args.input,
            name: args.input?.name || 'name',
            type: inputType,
        })
        input.classList.add(`${COMPONENT_CLASS_NAME}__input`)
        if (descriptionId) input.setAttribute('aria-describedby', descriptionId)
        if (args.required) input.setAttribute('required', 'true')
        wrapper.appendChild(input)
    }

    if (args.help) {
        const node = rzMessageRenderer({ text: args.help })
        node.classList.add(`${COMPONENT_CLASS_NAME}__message`)
        wrapper.appendChild(node)
    }

    if (args.error) {
        const node = rzMessageRenderer({ text: args.error, color: 'error' })
        node.classList.add(`${COMPONENT_CLASS_NAME}__message`)
        wrapper.appendChild(node)
    }

    return wrapper
}
