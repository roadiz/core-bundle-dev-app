import type { Meta, StoryObj } from '@storybook/html-vite'
import type { ButtonArgs } from './RzButton.stories'
import { rzButtonGroupRenderer } from '~/utils/storybook/renderer/rzButtonGroup'

const SPACING = ['sm', 'md', 'lg'] as const

export type Args = {
    collapsed?: boolean
    spacing?: (typeof SPACING)[number]
    buttons?: ButtonArgs[]
}

const meta: Meta<Args> = {
    title: 'Components/ButtonGroup',
    tags: ['autodocs'],
    args: {
        collapsed: false,
        spacing: 'md',
        buttons: [
            {
                iconClass: 'rz-icon-ri--upload-line',
                label: 'Upload',
                size: 'md',
                emphasis: 'medium',
            },
            {
                iconClass: 'rz-icon-ri--add-line',
                label: 'Explore',
                size: 'md',
                emphasis: 'medium',
            },
        ],
    },
    argTypes: {
        spacing: {
            options: SPACING,
            control: { type: 'select' },
            if: { arg: 'collapsed', eq: false },
            type: 'string',
            description: 'sm: 2px | md: 6px | lg: 8px',
        },
    },
    parameters: {
        layout: 'centered',
    },
}

export default meta
type Story = StoryObj<Args>

export const Default: Story = {
    render: (args) => {
        return rzButtonGroupRenderer(args)
    },
}

export const IconOnly: Story = {
    render: (args) => {
        return rzButtonGroupRenderer(args)
    },
    args: {
        spacing: 'sm',
        buttons: [
            {
                iconClass: 'rz-icon-ri--more-line',
                size: 'sm',
                emphasis: 'low',
            },
            {
                iconClass: 'rz-icon-ri--edit-line',
                size: 'sm',
                emphasis: 'low',
            },
            {
                iconClass: 'rz-icon-ri--delete-bin-7-line',
                size: 'sm',
                emphasis: 'low',
                color: 'error-light',
            },
        ],
    },
}

export const Collapsed: Story = {
    render: (args) => {
        return rzButtonGroupRenderer(args)
    },
    args: {
        collapsed: true,
        buttons: [
            {
                iconClass: 'rz-icon-ri--arrow-drop-left-line',
                size: 'lg',
                emphasis: 'medium',
            },
            {
                iconClass: 'rz-icon-ri--arrow-drop-down-line',
                size: 'lg',
                emphasis: 'medium',
            },
            {
                iconClass: 'rz-icon-ri--arrow-drop-right-line',
                size: 'lg',
                emphasis: 'medium',
            },
        ],
    },
}
