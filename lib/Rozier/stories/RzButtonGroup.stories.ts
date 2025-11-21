import type { Meta, StoryObj } from '@storybook/html-vite'
import type { ButtonArgs } from './RzButton.stories'
import { rzButtonGroupRenderer } from '~/utils/storybook/renderer/rzButtonGroup'

const GAPS = ['sm', 'md', 'lg'] as const
const SIZES = ['xs', 'sm', 'md', 'lg'] as const

export type Args = {
    collapsed?: boolean
    gap?: (typeof GAPS)[number]
    size?: (typeof SIZES)[number]
    buttons?: ButtonArgs[]
}

const meta: Meta<Args> = {
    title: 'Components/ButtonGroup',
    tags: ['autodocs'],
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
        size: 'lg',
        buttons: [
            {
                iconClass: 'rz-icon-ri--arrow-drop-left-line',
                emphasis: 'secondary',
            },
            {
                iconClass: 'rz-icon-ri--arrow-drop-down-line',
                emphasis: 'secondary',
            },
            {
                iconClass: 'rz-icon-ri--arrow-drop-right-line',
                emphasis: 'secondary',
            },
        ],
    },
}
