import type { ButtonArgs } from '../../../../stories/rzButton.stories'

export function buttonRenderer(
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

    const labelNode = document.createElement('span')
    labelNode.className = [`${className}__label`].join(' ')
    labelNode.innerText = args.label
    if (args.label) buttonNode.appendChild(labelNode)

    const iconNode = document.createElement('span')
    iconNode.className = [`${className}__icon`, args.iconClass].join(' ')
    if (args.iconClass) buttonNode.appendChild(iconNode)

    return buttonNode
}
