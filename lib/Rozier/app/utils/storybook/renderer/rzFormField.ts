import type { Args } from '../../../../stories/RzFormField.stories'
import { rzButtonGroupRenderer } from './rzButtonGroup'
import { rzInputRenderer } from './rzInput'
import { rzMessageRenderer } from './rzMessage'
import { rzColorInputRenderer } from './rzColorInput'
import { rzSwitchRenderer } from './rzSwitch'
import { rzBadgeRenderer } from '~/utils/component-renderer/rzBadge'

const COMPONENT_CLASS_NAME = 'rz-form-field'

/**
 * Renders the head portion of a form field, including label, icon, badge, and button group.
 * Can be used independently to render just the head section of a form field.
 *
 * @param {Args} args - Arguments describing the form field head properties.
 * @returns {HTMLDivElement} The head element for the form field.
 */
export function rzFormFieldHeadRenderer(args: Args) {
    const wrapperClass = `${COMPONENT_CLASS_NAME}__head`
    const head = document.createElement('div')
    head.classList.add(wrapperClass)
    if (args.headClass) head.classList.add(args.headClass)

    if (args.iconClass) {
        const icon = document.createElement('span')
        icon.classList.add(`${wrapperClass}__icon`, args.iconClass)
        head.appendChild(icon)
    }

    const label = document.createElement(args.input ? 'label' : 'span')
    label.classList.add(`${wrapperClass}__label`)
    label.textContent = args.label
    if (args.input) label.setAttribute('for', args.input?.id)
    head.appendChild(label)

    if (args.badge) {
        const badge = rzBadgeRenderer({
            ...args.badge,
            size: args.badge.size || 'xs',
        })
        badge.setAttribute('title', args.badge.title || 'Badge title')
        badge.classList.add(`${wrapperClass}__badge`)
        head.appendChild(badge)
    }

    if (args.maxLength || args.minLength) {
        const badge = rzBadgeRenderer({
            size: 'xs',
            label: `<span data-length-indicator-current>0</span>${args.maxLength ? '/' + args.maxLength : ''}`,
        })
        badge.setAttribute('data-length-indicator', 'rz-badge--danger')
        badge.classList.add(`${wrapperClass}__badge`)
        head.appendChild(badge)
    }

    if (args.minLength) {
        const badge = rzBadgeRenderer({
            size: 'xs',
            color: 'information',
            label: `min: ${args.minLength}`,
        })
        badge.classList.add(`${wrapperClass}__badge`)
        head.appendChild(badge)
    }

    if (args.buttonGroup) {
        const buttonGroup = rzButtonGroupRenderer(args.buttonGroup)
        buttonGroup.classList.add(`${wrapperClass}__end`)
        head.appendChild(buttonGroup)
    }

    return head
}

export function rzFormFieldRenderer(
    args: Args,
    mainElement?: HTMLElement | null,
) {
    const wrapper = document.createElement('rz-form-field')
    const inputType = args.input?.type || args.type

    const wrapperClasses = [
        COMPONENT_CLASS_NAME,
        args.required && `${COMPONENT_CLASS_NAME}--required`,
        args.horizontal && `${COMPONENT_CLASS_NAME}--horizontal`,
        args.alignStart && `${COMPONENT_CLASS_NAME}--align-start`,
        args.error && `${COMPONENT_CLASS_NAME}--error`,
    ].filter((c) => c) as string[]
    wrapper.classList.add(...wrapperClasses)

    const head = rzFormFieldHeadRenderer(args)
    wrapper.appendChild(head)

    let descriptionId: string | undefined = undefined
    if (args.description) {
        const description = document.createElement('p')
        description.classList.add(`${COMPONENT_CLASS_NAME}__description`)
        description.textContent = args.description
        if (args.input?.name) {
            descriptionId = `${args.input.name}-description-${Date.now()}`
            description.id = descriptionId
        }
        wrapper.appendChild(description)
    }

    if (mainElement) {
        mainElement.classList.add(`${COMPONENT_CLASS_NAME}__body`)
        wrapper.appendChild(mainElement)
    } else if (args.input) {
        const renderer = args.input.className?.includes('rz-switch')
            ? rzSwitchRenderer
            : args.input?.type === 'color'
              ? rzColorInputRenderer
              : rzInputRenderer

        const input = renderer({
            ...args.input,
            name: args.input?.name || 'name',
            type: inputType,
            attributes: {
                ...args.input?.attributes,
                'data-max-length': args.maxLength
                    ? String(args.maxLength)
                    : undefined,
                'data-min-length': args.minLength
                    ? String(args.minLength)
                    : undefined,
            },
        })
        input.classList.add(`${COMPONENT_CLASS_NAME}__body`)
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
