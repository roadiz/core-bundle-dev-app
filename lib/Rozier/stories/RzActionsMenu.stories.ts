import type { Meta, StoryObj } from '@storybook/html-vite'
import {
    type RzActionsMenuOptions,
    rzActionsMenuRenderer,
} from '~/utils/storybook/renderer/rzActionsMenu'

type Args = RzActionsMenuOptions

const meta: Meta<Args> = {
    title: 'Components/Actions/ActionsMenu',
    tags: ['autodocs'],
    render: (args) => rzActionsMenuRenderer(args),
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
                    emphasis: 'primary',
                    iconClass: 'rz-icon-ri--delete-bin-7-line',
                    size: 'md',
                    color: 'danger',
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
                    emphasis: 'primary',
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

export const Default: Story = {}

export const Vertical: Story = {
    args: {
        vertical: true,
    },
}
