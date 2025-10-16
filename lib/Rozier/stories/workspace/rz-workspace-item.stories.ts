import type { Meta, StoryObj } from '@storybook/html-vite'
import '../../app/assets/css/main.css'

const COMPONENT_CLASS = 'rz-workspace-item'

type ButtonArgs = {
    label: string
    selected: boolean
    iconClass: string
    variants: 'level-1' | 'level-2'
    tag: string
}

const meta: Meta<ButtonArgs> = {
    title: 'Components/Workspace/Item',
    // tags: ['autodocs'],
    args: {
        label: 'Workspace item label',
        selected: false,
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
type Story = StoryObj<ButtonArgs>

function iconRenderer(iconClass: string) {
    if (!iconClass) return undefined
    const icon = document.createElement('span')
    icon.classList.add(`${COMPONENT_CLASS}__icon`, iconClass)

    return icon
}

function itemRenderer(args: ButtonArgs) {
    const node = document.createElement(args.tag)
    const variantClass = args.variants
        ? `${COMPONENT_CLASS}--${args.variants}`
        : ''
    const selectedClass = args.selected ? `${COMPONENT_CLASS}--selected` : ''
    const classes = [COMPONENT_CLASS, variantClass, selectedClass].filter(
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

export const Default: Story = {
    render: (args) => {
        return itemRenderer(args)
    },
}

export const Dropdown: Story = {
    render: (args) => {
        const item = itemRenderer(args)
        item.setAttribute('aria-expanded', 'false')

        item.addEventListener('click', (e) => {
            const el = e.currentTarget as HTMLElement
            if (!el) return
            const expanded = el.getAttribute('aria-expanded') === 'true'
            el.setAttribute('aria-expanded', expanded ? 'false' : 'true')
        })

        const dropdownIcon = document.createElement('span')
        dropdownIcon.classList.add(
            `${COMPONENT_CLASS}__arrow`,
            'rz-icon-ri--arrow-down-s-line',
        )

        item.appendChild(dropdownIcon)

        return item
    },
    args: {
        tag: 'button',
        iconClass: 'rz-icon-ri--computer-line',
    },
    parameters: {
        controls: { exclude: ['tag'] },
    },
}

export const Link: Story = {
    render: (args, context) => {
        const item = itemRenderer(args)
        console.log(context)
        item.setAttribute('href', window.location.href)
        const dropdownIcon = document.createElement('span')
        dropdownIcon.classList.add(
            `${COMPONENT_CLASS}__arrow`,
            'rz-icon-ri--arrow-right-s-line',
        )

        item.appendChild(dropdownIcon)

        return item
    },
    args: {
        tag: 'a',
    },
}
