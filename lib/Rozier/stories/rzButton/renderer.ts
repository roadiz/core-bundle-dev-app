import type { ButtonArgs } from './types'
import { SIZES, COMPONENT_CLASS_NAME } from './constants'

export function buttonRenderer(
    args: ButtonArgs,
    attrs: Record<string, string> = {},
) {
    const buttonNode = document.createElement('button')
    const attributesEntries = Object.entries(attrs)
    if (attributesEntries.length) {
        attributesEntries.forEach(([key, value]) => {
            buttonNode.setAttribute(key, value)
        })
    }
    const emphasisClass = args.emphasis
        ? `${COMPONENT_CLASS_NAME}--emphasis-${args.emphasis}`
        : ''
    const sizeClass = args.size
        ? `${COMPONENT_CLASS_NAME}--size-${args.size}`
        : ''
    const disabledClass = args.disabled
        ? `${COMPONENT_CLASS_NAME}--disabled`
        : ''
    const onDarkClass = args.onDark ? `${COMPONENT_CLASS_NAME}--on-dark` : ''

    buttonNode.className = [
        COMPONENT_CLASS_NAME,
        emphasisClass,
        sizeClass,
        disabledClass,
        onDarkClass,
        args.additionalClasses,
    ]
        .filter((c) => !!c)
        .join(' ')
        .trim()

    const labelNode = document.createElement('span')
    labelNode.className = [`${COMPONENT_CLASS_NAME}__label`].join(' ')
    labelNode.innerText = args.label
    if (args.label) buttonNode.appendChild(labelNode)

    const iconNode = document.createElement('span')
    iconNode.className = [`${COMPONENT_CLASS_NAME}__icon`, args.iconClass].join(
        ' ',
    )
    if (args.iconClass) buttonNode.appendChild(iconNode)

    return buttonNode
}

export function buttonSizeListRenderer(args: ButtonArgs) {
    const wrapper = document.createElement('div')
    wrapper.style =
        'display: flex; gap: 16px; flex-wrap: wrap; align-items: center;'

    SIZES.forEach((size) => {
        const btn = buttonRenderer({
            ...args,
            size,
            label: `${args.emphasis || 'unknown'} emphasis ${size}`,
        })
        wrapper.appendChild(btn)

        const btnIconOnly = buttonRenderer({ ...args, size, label: `` })
        wrapper.appendChild(btnIconOnly)
    })

    return wrapper
}
