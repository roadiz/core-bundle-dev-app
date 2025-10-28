import type { Meta, StoryObj } from '@storybook/html-vite'
import { rzButtonRenderer } from '../app/utils/storybook/renderer/rzButton'

export type Args = {
    label?: string
}

const meta: Meta<Args> = {
    title: 'Components/ButtonGroup',
    args: {},
    parameters: {
        layout: 'centered',
    },
}

export default meta
type Story = StoryObj<Args>

export const Default: Story = {
    render: () => {
        const wrapper = document.createElement('div')
        wrapper.className = 'rz-button-group'

        const iconNames = [
            'rz-icon-ri--arrow-drop-left-line',
            'rz-icon-ri--arrow-drop-down-line',
            'rz-icon-ri--arrow-drop-right-line',
        ]

        iconNames.forEach((iconClass, index) => {
            const button = rzButtonRenderer(
                {
                    iconClass,
                    size: 'lg',
                },
                { 'aria-label': `Button ${index + 1}` },
            )
            wrapper.appendChild(button)
        })

        return wrapper
    },
}
