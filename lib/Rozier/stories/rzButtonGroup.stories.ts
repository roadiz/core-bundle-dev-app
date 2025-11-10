import type { Meta, StoryObj } from '@storybook/html-vite'
import type { ButtonArgs } from './rzButton.stories'
import { rzButtonGroupRenderer } from '~/utils/storybook/renderer/rzButtonGroup'

const SIZES = ['sm', 'md', 'lg'] as const

export type Args = {
    collapsed?: boolean
    size?: (typeof SIZES)[number]
    buttons?: ButtonArgs[]
    additionalClass?: string
}

const meta: Meta<Args> = {
    title: 'Components/ButtonGroup',
    tags: ['autodocs'],
    args: {
        collapsed: false,
        size: 'md',
        buttons: [
            {
                iconClass: 'rz-icon-ri--upload-line',
                label: 'Upload',
                emphasis: 'medium',
            },
            {
                iconClass: 'rz-icon-ri--add-line',
                label: 'Explore',
                emphasis: 'medium',
            },
        ],
        additionalClass: 'rz-button--md',
    },
    argTypes: {
        size: {
            options: SIZES,
            control: { type: 'select' },
            if: { arg: 'collapsed', eq: false },
            type: 'string',
            description:
                'Gap between children: sm -> 2px | md -> 6px | lg -> 8px',
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
        additionalClass: 'rz-button--sm',
        buttons: [
            {
                iconClass: 'rz-icon-ri--more-line',
                emphasis: 'low',
            },
            {
                iconClass: 'rz-icon-ri--edit-line',
                emphasis: 'low',
            },
            {
                iconClass: 'rz-icon-ri--delete-bin-7-line',
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
