import type { Meta, StoryObj } from '@storybook/html-vite'
import { ButtonArgs } from './RzButton.stories'
import { rzButtonRenderer } from '../app/utils/storybook/renderer/rzButton'

const COMPONENT_CLASS_NAME = 'rz-floating-bar'

export type Args = {
    vertical?: boolean
    items: {
        popoverContent?: string
        button: ButtonArgs
    }[]
}

const meta: Meta<Args> = {
    title: 'Components/FloatingBar',
    tags: ['autodocs'],
    args: {
        vertical: false,
        items: [
            {
                popoverContent: 'Popover content',
                button: {
                    emphasis: 'tertiary',
                    iconClass: 'rz-icon-ri--settings-4-line',
                    size: 'md',
                    onDark: true,
                    attributes: { 'aria-label': 'Settings' },
                },
            },
            {
                button: {
                    emphasis: 'tertiary',
                    iconClass: 'rz-icon-ri--checkbox-circle-line',
                    size: 'md',
                    onDark: true,
                    attributes: { 'aria-label': 'Checkbox Circle' },
                },
            },
            {
                button: {
                    emphasis: 'tertiary',
                    iconClass: 'rz-icon-ri--history-line',
                    size: 'md',
                    onDark: true,
                    attributes: { 'aria-label': 'History' },
                },
            },
            {
                button: {
                    tag: 'a',
                    iconClass: 'rz-icon-ri--delete-bin-7-line',
                    size: 'md',
                    color: 'error',
                    onDark: true,
                    attributes: {
                        'aria-label': 'Delete',
                        href: '#',
                    },
                },
            },
            {
                button: {
                    tag: 'a',
                    iconClass: 'rz-icon-ri--save-line',
                    size: 'md',
                    color: 'success',
                    onDark: true,
                    attributes: {
                        'aria-label': 'Save',
                        href: '#',
                    },
                },
            },
        ],
    },
    parameters: {
        layout: 'centered',
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

    args.items.forEach((item) => {
        const button = rzButtonRenderer(item.button)

        // TODO: implement when rz-popover-content is ready
        if (item.popoverContent) {
            const popoverWrapper = document.createElement('div')
            popoverWrapper.appendChild(button)

            const p = document.createElement('p')
            p.style.display = 'none'
            p.textContent = item.popoverContent
            popoverWrapper.appendChild(p)

            wrapper.appendChild(popoverWrapper)
        } else {
            wrapper.appendChild(button)
        }
    })

    return wrapper
}

export const Default: Story = {
    render: (args) => {
        return rzFloatingBar(args)
    },
}

export const Vertical: Story = {
    render: (args) => {
        return rzFloatingBar(args)
    },
    args: {
        vertical: true,
    },
}
