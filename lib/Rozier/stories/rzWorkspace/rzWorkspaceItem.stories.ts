import type { Meta, StoryObj } from '@storybook/html-vite'
import { itemRenderer, arrowIconRenderer } from './renderer'
import type { ItemArgs } from './types'

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
