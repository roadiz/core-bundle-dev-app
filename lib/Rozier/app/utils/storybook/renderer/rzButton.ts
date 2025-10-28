import type { ButtonArgs } from '../../../../stories/rzButton.stories'

export function rzButtonRenderer(
    args: ButtonArgs,
    attrs?: Record<string, string>,
) {
    const className = 'rz-button'
    const buttonNode = document.createElement('button')
    const attributesEntries = Object.entries(attrs || {})
    if (attributesEntries.length) {
        attributesEntries.forEach(([key, value]) => {
            buttonNode.setAttribute(key, value)
        })
    }
    const emphasisClass =
        args.emphasis && `${className}--emphasis-${args.emphasis}`
    const sizeClass = args.size && `${className}--size-${args.size}`
    const disabledClass = args.disabled && `${className}--disabled`
    const onDarkClass = args.onDark && `${className}--on-dark`

    buttonNode.className = [
        className,
        emphasisClass,
        sizeClass,
        disabledClass,
        onDarkClass,
        args.additionalClasses,
    ]
        .filter((c) => !!c)
        .join(' ')
        .trim()

    if (args.label) {
        const labelNode = document.createElement('span')
        labelNode.className = [`${className}__label`].join(' ')
        labelNode.innerText = args.label
        buttonNode.appendChild(labelNode)
    }

    if (args.iconClass) {
        const iconNode = document.createElement('span')
        iconNode.className = [`${className}__icon`, args.iconClass].join(' ')
        buttonNode.appendChild(iconNode)
    }

    return buttonNode
}
