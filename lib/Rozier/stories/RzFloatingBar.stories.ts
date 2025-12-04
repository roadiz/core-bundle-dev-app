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
                popoverContent:
                    '<div popover id="popover1">Popover content</div>',
                button: {
                    emphasis: 'tertiary',
                    iconClass: 'rz-icon-ri--settings-4-line',
                    size: 'md',
                    onDark: true,
                    attributes: {
                        'aria-label': 'Settings',
                        popovertarget: 'popover1',
                    },
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

        if (item.popoverContent) {
            const popoverWrapper = document.createElement('rz-popover')
            popoverWrapper.setAttribute('data-popover-offset', '24px')
            popoverWrapper.setAttribute('data-popover-placement', 'top-start')
            popoverWrapper.innerHTML = item.popoverContent
            popoverWrapper.appendChild(button)

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
