import type { Meta, StoryObj } from '@storybook/html-vite'
import { rzButtonRenderer } from '../app/utils/storybook/renderer/rzButton'

const EMPHASIS = ['tertiary', 'secondary', 'primary'] as const
const SIZES = ['xs', 'sm', 'md', 'lg'] as const
const COLORS = ['success', 'success-light', 'error', 'error-light'] as const

export type ButtonArgs = {
    label?: string
    emphasis?: (typeof EMPHASIS)[number]
    size?: (typeof SIZES)[number]
    disabled?: boolean
    iconClass?: string
    onDark?: boolean
    additionalClasses?: string
    color?: (typeof COLORS)[number]
    attributes?: Record<string, string>
}

const meta: Meta<ButtonArgs> = {
    title: 'Components/Button',
    tags: ['autodocs'],
    args: {
        label: 'button label',
        disabled: false,
        iconClass: 'rz-icon-ri--arrow-drop-right-line',
        onDark: false,
    },
    argTypes: {
        label: {
            description:
                'Text label inside the button. Could be empty for icon only buttons.',
            table: {
                elementClass: 'rz-button__label',
            },
        },
        disabled: {
            description: 'Add rz-button--disabled class',
        },
        iconClass: {
            description:
                'Add rz-button__icon and rz-icon-{collection}--{iconName}',
        },
        emphasis: {
            control: { type: 'select' },
            options: [...EMPHASIS, ''],
            type: 'string',
            description:
                'If no emphasis class is provided, emphasis medium is applied by default.',
        },
        size: {
            control: { type: 'select' },
            options: [...SIZES, ''],
            type: 'string',
            description:
                'If no size class is provided, size md is applied by default.',
        },
        color: {
            control: { type: 'select' },
            options: [...COLORS, ''],
            type: 'string',
        },
    },
    parameters: {
        layout: 'centered',
    },
}

export default meta
type Story = StoryObj<ButtonArgs>

export const Default: Story = {
    render: (args) => {
        return rzButtonRenderer(args)
    },
}

export const HighEmphasis: Story = {
    render: (args) => {
        return rzButtonRenderer(args)
    },
    args: {
        emphasis: 'primary',
    },
    parameters: {
        controls: { exclude: ['emphasis', 'additionalClasses'] },
        layout: 'centered',
    },
}

export const HighEmphasisList: Story = {
    render: (args) => {
        return buttonSizeListRenderer(args)
    },
    args: {
        emphasis: 'primary',
    },
    parameters: {
        controls: {
            exclude: ['emphasis', 'size', 'label', 'additionalClasses'],
        },
    },
}

export const MediumEmphasis: Story = {
    render: (args) => {
        return rzButtonRenderer(args)
    },
    args: {
        emphasis: 'secondary',
    },
    parameters: {
        controls: { exclude: ['emphasis', 'class'] },
    },
}

export const MediumEmphasisList: Story = {
    render: (args) => {
        return buttonSizeListRenderer(args)
    },
    args: {
        emphasis: 'secondary',
    },
    parameters: {
        controls: {
            exclude: ['emphasis', 'size', 'label', 'additionalClasses'],
        },
    },
}

export const LowEmphasis: Story = {
    render: (args) => {
        return rzButtonRenderer(args)
    },
    args: {
        emphasis: 'tertiary',
    },
    parameters: {
        controls: { exclude: ['emphasis'] },
    },
}

export const LowEmphasisList: Story = {
    render: (args) => {
        return buttonSizeListRenderer(args)
    },
    args: {
        emphasis: 'tertiary',
    },
    parameters: {
        controls: {
            exclude: ['emphasis', 'size', 'label', 'additionalClasses'],
        },
    },
}

export const LiveClassesEditing: Story = {
    render: (args) => {
        return rzButtonRenderer(args)
    },
    args: {
        emphasis: undefined,
        size: undefined,
        additionalClasses:
            'rz-button--emphasis-high rz-button--size-lg rz-button--disabled',
    },
    parameters: {
        controls: { exclude: ['emphasis', 'size', 'disabled'] },
    },
}

export const DisabledList: Story = {
    render: (args) => {
        const rootNode = document.createElement('div')
        rootNode.classList.add('story-container')
        rootNode.style =
            'display: flex; flex-direction: column; gap: 24px; align-items: center;'

        EMPHASIS.forEach((emphasis) => {
            const btn = buttonSizeListRenderer({ ...args, emphasis })
            rootNode.appendChild(btn)
        })

        return rootNode
    },
    args: {
        disabled: true,
    },
    parameters: {
        controls: {
            include: [],
        },
    },
}

/* RENDERER */
function buttonSizeListRenderer(args: ButtonArgs) {
    const wrapper = document.createElement('div')
    wrapper.style =
        'display: flex; gap: 16px; flex-wrap: wrap; align-items: center;'

    SIZES.forEach((size) => {
        const btn = rzButtonRenderer({
            ...args,
            size,
            label: `${args.emphasis || 'unknown'} emphasis ${size}`,
        })
        wrapper.appendChild(btn)

        const btnIconOnly = rzButtonRenderer({ ...args, size, label: `` })
        wrapper.appendChild(btnIconOnly)
    })

    return wrapper
}
