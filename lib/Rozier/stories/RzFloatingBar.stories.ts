import type { Meta, StoryObj } from '@storybook/html-vite'
import { ButtonArgs } from './RzButton.stories'
import { rzButtonRenderer } from '../app/utils/storybook/renderer/rzButton'

const COMPONENT_CLASS_NAME = 'rz-floating-bar'

export type Args = {
    vertical?: boolean
    buttons: ButtonArgs[]
}

const meta: Meta<Args> = {
    title: 'Components/FloatingBar',
    tags: ['autodocs'],
    args: {
        vertical: false,
        buttons: [
            {
                emphasis: 'tertiary',
                iconClass: 'rz-icon-ri--settings-4-line',
                size: 'md',
                onDark: true,
                attributes: { 'aria-label': 'Settings' },
            },
            {
                emphasis: 'tertiary',
                iconClass: 'rz-icon-ri--checkbox-circle-line',
                size: 'md',
                onDark: true,
                attributes: { 'aria-label': 'Checkbox Circle' },
            },
            {
                emphasis: 'tertiary',
                iconClass: 'rz-icon-ri--history-line',
                size: 'md',
                onDark: true,
                attributes: { 'aria-label': 'History' },
            },
            {
                emphasis: 'primary',
                iconClass: 'rz-icon-ri--delete-bin-7-line',
                size: 'md',
                color: 'error',
                onDark: true,
                attributes: { 'aria-label': 'Delete' },
            },
            {
                emphasis: 'primary',
                iconClass: 'rz-icon-ri--save-line',
                size: 'md',
                color: 'success',
                onDark: true,
                attributes: { 'aria-label': 'Save' },
            },
        ],
    },
}

export default meta
type Story = StoryObj<Args>

function rzFloatingBar(args: Args) {
    const wrapper = document.createElement('div')
    wrapper.classList.add(COMPONENT_CLASS_NAME)
    if (args.vertical) {
        wrapper.classList.add(`${COMPONENT_CLASS_NAME}--vertical`)
    }

    args.buttons.forEach((buttonArgs) => {
        const button = rzButtonRenderer(buttonArgs)
        wrapper.appendChild(button)
    })

    return wrapper
}

export const Default: Story = {
    render: (args) => {
        return rzFloatingBar(args)
    },
}
