import type { ButtonArgs } from '../../../../stories/RzButton.stories'

const COMPONENT_CLASS_NAME = 'rz-button'
const COMPONENT_CLASS = 'rz-button'

export function rzButtonRenderer(args: ButtonArgs) {
    const buttonNode = document.createElement('button', { is: COMPONENT_CLASS })
    const attributesEntries = Object.entries(args.attributes || {})

    if (attributesEntries.length) {
        attributesEntries.forEach(([key, value]) => {
            if (typeof value === 'undefined') return
            buttonNode.setAttribute(key, value)
        })
    }

    // Add classes after setting attributes to avoid overwriting class attribute
    buttonNode.classList.add(COMPONENT_CLASS_NAME)
    if (args.emphasis) {
        buttonNode.classList.add(`${COMPONENT_CLASS}--${args.emphasis}`)
    }
    if (args.size) {
        buttonNode.classList.add(`${COMPONENT_CLASS}--${args.size}`)
    }
    if (args.disabled) {
        buttonNode.classList.add(`${COMPONENT_CLASS}--disabled`)
    }
    if (args.onDark) {
        buttonNode.classList.add(`${COMPONENT_CLASS}--on-dark`)
    }
    if (args.color) {
        buttonNode.classList.add(`${COMPONENT_CLASS}--${args.color}`)
    }
    if (args.hasPill) {
        buttonNode.classList.add(`${COMPONENT_CLASS}--pill`)
    }
    if (args.selected) {
        buttonNode.classList.add(`${COMPONENT_CLASS}--selected`)
    }

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
