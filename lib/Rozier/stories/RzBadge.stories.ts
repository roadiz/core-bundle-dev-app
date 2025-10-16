import type { Meta, StoryObj } from '@storybook/html-vite'
import '../app/assets/css/main.css'

const COMPONENT_CLASS = 'rz-badge'

type BadgeArgs = {
    label: string
    iconClass: string
}

const meta: Meta<BadgeArgs> = {
    title: 'Components/Badge',
    args: {
        label: 'My badge',
        iconClass: 'rz-icon-ri--computer-line',
    },
}

export default meta
type Story = StoryObj<BadgeArgs>

function iconRenderer(iconClass: string) {
    if (!iconClass) return undefined
    const icon = document.createElement('span')
    icon.classList.add(`${COMPONENT_CLASS}__icon`, iconClass)

    return icon
}

function itemRenderer(args: BadgeArgs) {
    const node = document.createElement('div')
    const classes = [COMPONENT_CLASS].filter((c) => c)
    node.classList.add(...classes)

    const icon = iconRenderer(args.iconClass)
    if (icon) {
        node.appendChild(icon)
    }

    const label = document.createTextNode(args.label)
    node.appendChild(label)

    return node
}

export const Default: Story = {
    render: (args) => {
        return itemRenderer(args)
    },
}
