import type { Meta, StoryObj } from '@storybook/html-vite'
import { defineLazyElement } from '../../app/utils/custom-element/defineLazyElement'
import customElementList from '../../app/custom-elements'
import { itemRenderer, arrowIconRenderer } from './renderer'
import type { ItemArgs } from './types'

import '../../app/assets/css/main.css'

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

function registerCustomElement(name: string) {
    if (!window.customElements.get(name)) {
        // @ts-expect-error Ignore dynamic module type
        defineLazyElement(name, customElementList[name])
    }
}

export const ButtonCustomElement: Story = {
    render: (args) => {
        const customComponentName = 'rz-workspace-item-button'
        registerCustomElement(customComponentName)

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
