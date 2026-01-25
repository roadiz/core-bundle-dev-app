import type { Meta, StoryObj } from '@storybook/html-vite'

const COMPONENT_CLASS = 'rz-badge'
const SIZES = ['xs', 'sm', 'md'] as const
const COLORS = ['information', 'success', 'warning', 'error'] as const

type BadgeArgs = {
    label: string
    iconClass: string
    size: (typeof SIZES)[number]
    color: (typeof COLORS)[number]
}

const meta: Meta<BadgeArgs> = {
    title: 'Components/RzBadge',
    args: {
        label: 'My badge',
        iconClass: 'rz-icon-ri--add-line',
    },
    argTypes: {
        size: {
            options: ['', ...SIZES],
            control: { type: 'radio' },
        },
        color: {
            options: ['', ...COLORS],
            control: { type: 'radio' },
        },
    },
    globals: {
        backgrounds: { value: 'light' },
    },
    parameters: {
        layout: 'centered',
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

function labelRenderer(text: string) {
    const label = document.createElement('span')
    label.classList.add(`${COMPONENT_CLASS}__label`)
    label.textContent = text

    return label
}

function itemRenderer(args: BadgeArgs) {
    const node = document.createElement('div')
    const sizeClass = args.size ? `${COMPONENT_CLASS}--size-${args.size}` : ''
    const statusClass = args.color && `${COMPONENT_CLASS}--${args.color}`
    const classes = [COMPONENT_CLASS, sizeClass, statusClass].filter((c) => c)
    node.classList.add(...classes)

    const icon = iconRenderer(args.iconClass)
    if (icon) {
        node.appendChild(icon)
    }

    const label = labelRenderer(args.label)
    node.appendChild(label)

    return node
}

export const Default: Story = {
    render: (args) => {
        return itemRenderer(args)
    },
}

export const Information: Story = {
    render: (args) => {
        return itemRenderer({
            ...args,
            label: 'Information',
            color: 'information',
            iconClass: 'rz-icon-rz--status-published-colored',
        })
    },
}

export const Published: Story = {
    render: (args) => {
        return itemRenderer({
            ...args,
            label: 'Published',
            color: 'success',
            iconClass: 'rz-icon-rz--status-published-colored',
        })
    },
}

export const Draft: Story = {
    render: (args) => {
        return itemRenderer({
            ...args,
            label: 'Draft',
            color: 'warning',
            iconClass: 'rz-icon-rz--status-draft-colored',
        })
    },
}

export const Unpublished: Story = {
    render: (args) => {
        return itemRenderer({
            ...args,
            label: 'Unpublished',
            color: 'error',
            iconClass: 'rz-icon-rz--status-draft-colored',
        })
    },
}
