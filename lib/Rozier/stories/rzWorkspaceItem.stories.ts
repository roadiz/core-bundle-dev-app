import type { Meta, StoryObj } from '@storybook/html-vite'

export type ItemArgs = {
    label: string
    active: boolean
    iconClass: string
    variants: 'level-1' | 'level-2'
    tag: string
}

const COMPONENT_CLASS_NAME = 'rz-workspace-item'

const meta: Meta<ItemArgs> = {
    title: 'Components/RzWorkspace/Item',
    args: {
        label: 'Workspace item label',
        active: false,
        iconClass: 'rz-icon-ri--computer-line',
        variants: 'level-1',
        tag: 'div',
    },
    argTypes: {
        variants: {
            options: ['level-1', 'level-2'],
            control: { type: 'radio' },
        },
    },
    globals: {
        backgrounds: { value: 'light' },
    },
}

export default meta
type Story = StoryObj<ItemArgs>

export const Default: Story = {
    render: (args) => {
        return itemRenderer(args)
    },
}

export const Link: Story = {
    render: (args) => {
        const item = itemRenderer(args)
        item.setAttribute('href', window.location.href)

        const arrowIcon = arrowIconRenderer('right')
        item.appendChild(arrowIcon)

        return item
    },
    args: {
        tag: 'a',
    },
}

export const ButtonCustomElement: Story = {
    render: (args) => {
        const customComponentName = 'rz-workspace-item-button'
        const button = itemRenderer(args, { is: customComponentName })

        const dropdownIcon = arrowIconRenderer()
        button.appendChild(dropdownIcon)

        return button
    },
    args: {
        tag: 'button',
        iconClass: 'rz-icon-ri--computer-line',
    },
    parameters: {
        controls: { exclude: ['tag'] },
    },
}

export function iconRenderer(iconClass: string) {
    if (!iconClass) return undefined
    const icon = document.createElement('span')
    icon.classList.add(`${COMPONENT_CLASS_NAME}__icon`, iconClass)

    return icon
}

/* DOM renderer */
function itemRenderer(
    args: ItemArgs,
    createElementOptions?: ElementCreationOptions,
) {
    const node = document.createElement(args.tag, createElementOptions)
    if (createElementOptions?.is) {
        node.setAttribute('is', createElementOptions.is)
    }
    const variantClass = args.variants
        ? `${COMPONENT_CLASS_NAME}--${args.variants}`
        : ''
    const activeClass = args.active ? `${COMPONENT_CLASS_NAME}--active` : ''
    const classes = [COMPONENT_CLASS_NAME, variantClass, activeClass].filter(
        (c) => c,
    )
    node.classList.add(...classes)

    const icon = iconRenderer(args.iconClass)
    if (icon) {
        node.appendChild(icon)
    }

    const label = document.createTextNode(args.label)
    node.appendChild(label)

    return node
}

function arrowIconRenderer(direction: 'down' | 'right' = 'down') {
    const dropdownIcon = document.createElement('span')
    dropdownIcon.classList.add(
        `${COMPONENT_CLASS_NAME}__arrow`,
        `rz-icon-ri--arrow-${direction}-s-line`,
    )

    return dropdownIcon
}
