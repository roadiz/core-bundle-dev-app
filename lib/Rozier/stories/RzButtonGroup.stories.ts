import type { Meta, StoryObj } from '@storybook/html-vite'
import {
    GAPS,
    type RzButtonGroupOptions,
    rzButtonGroupRenderer,
    SIZES,
} from '~/utils/component-renderer/rzButtonGroup'

type Args = RzButtonGroupOptions

const meta: Meta<Args> = {
    title: 'Components/Actions/ButtonGroup',
    tags: ['autodocs'],
    render: (args) => rzButtonGroupRenderer(args),
    args: {
        collapsed: false,
        gap: 'md',
        buttons: [
            {
                iconClass: 'rz-icon-ri--upload-line',
                label: 'Upload',
                emphasis: 'secondary',
            },
            {
                iconClass: 'rz-icon-ri--add-line',
                label: 'Explore',
                emphasis: 'secondary',
            },
        ],
    },
    argTypes: {
        size: {
            options: ['', ...SIZES],
            control: { type: 'select' },
            type: 'string',
            description: 'Set button children size',
        },
        gap: {
            options: ['', ...GAPS],
            control: { type: 'select' },
            if: { arg: 'collapsed', eq: false },
            type: 'string',
            description: 'Gap between children: sm: 2px | md: 6px | lg: 8px',
            table: {
                defaultValue: { summary: 'md' },
            },
        },
    },
    parameters: {
        layout: 'centered',
    },
}

export default meta
type Story = StoryObj<Args>

export const Default: Story = {}

export const IconOnly: Story = {
    args: {
        size: 'sm',
        gap: 'sm',
        buttons: [
            {
                iconClass: 'rz-icon-ri--more-line',
                emphasis: 'tertiary',
            },
            {
                iconClass: 'rz-icon-ri--edit-line',
                emphasis: 'tertiary',
            },
            {
                iconClass: 'rz-icon-ri--delete-bin-7-line',
                emphasis: 'tertiary',
                color: 'danger',
            },
        ],
    },
}

export const Collapsed: Story = {
    args: {
        collapsed: true,
        size: 'lg',
        buttons: [
            {
                iconClass: 'rz-icon-ri--arrow-drop-left-line',
                emphasis: 'secondary',
                attributes: {
                    'tooltip-text': 'Left',
                    is: 'rz-button',
                },
            },
            {
                iconClass: 'rz-icon-ri--arrow-drop-down-line',
                emphasis: 'secondary',
                attributes: {
                    'tooltip-text': 'Down',
                    is: 'rz-button',
                },
            },
            {
                iconClass: 'rz-icon-ri--arrow-drop-right-line',
                emphasis: 'secondary',
                attributes: {
                    'tooltip-text': 'Right',
                    is: 'rz-button',
                },
            },
        ],
    },
}
