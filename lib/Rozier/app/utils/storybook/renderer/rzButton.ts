import type { ButtonArgs } from '../../../../stories/RzButton.stories'

const COMPONENT_CLASS_NAME = 'rz-button'

export function rzButtonRenderer(args: ButtonArgs) {
    const buttonNode = document.createElement(args.tag || 'rz-button')
    const attributesEntries = Object.entries(args.attributes || {})
    if (attributesEntries.length) {
        attributesEntries.forEach(([key, value]) => {
            buttonNode.setAttribute(key, value)
        })
    }
    const emphasisClass =
        args.emphasis && `${COMPONENT_CLASS_NAME}--${args.emphasis}`
    const sizeClass = args.size && `${COMPONENT_CLASS_NAME}--${args.size}`
    const selectedClass = args.selected && `${COMPONENT_CLASS_NAME}--selected`
    const disabledClass = args.disabled && `${COMPONENT_CLASS_NAME}--disabled`
    const onDarkClass = args.onDark && `${COMPONENT_CLASS_NAME}--on-dark`
    const colorClass = args.color && `${COMPONENT_CLASS_NAME}--${args.color}`
    const pillClass = args.hasPill && `${COMPONENT_CLASS_NAME}--pill`

    buttonNode.className = [
        COMPONENT_CLASS_NAME,
        pillClass,
        emphasisClass,
        sizeClass,
        selectedClass,
        disabledClass,
        onDarkClass,
        colorClass,
        args.additionalClasses,
    ]
        .filter((c) => !!c)
        .join(' ')
        .trim()

    if (args.label) {
        const labelNode = document.createElement('span')
        labelNode.className = [`${COMPONENT_CLASS_NAME}__label`].join(' ')
        labelNode.innerText = args.label
        buttonNode.appendChild(labelNode)
    }

    if (args.iconClass) {
        const iconNode = document.createElement('span')
        iconNode.className = [
            `${COMPONENT_CLASS_NAME}__icon`,
            args.iconClass,
        ].join(' ')
        buttonNode.appendChild(iconNode)
    }

    return buttonNode
}
