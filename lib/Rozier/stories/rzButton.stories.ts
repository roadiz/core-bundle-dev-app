import type { Meta, StoryObj } from '@storybook/html-vite'
import { rzButtonRenderer } from '../app/utils/storybook/renderer/rzButton'

const EMPHASIS = ['low', 'medium', 'high'] as const
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
}

/** Use `.rz-button` class on a button element to create a styled button. Optionally add emphasis and size modifier classes.
 * In most case you will use a label and /or an icon inside the button. add `.rz-button__label` and/or `.rz-button__icon` classes to the inner elements.
 *
 * ### Emphasis modifier classes
 * - `.rz-button--emphasis-low`
 * - `.rz-button--emphasis-medium` (default)
 * - `.rz-button--emphasis-high`
 *
 * ### Size modifier classes
 * - `.rz-button--size-xs`
 * - `.rz-button--size-sm`
 * - `.rz-button--size-md` (default)
 * - `.rz-button--size-lg–`
 *
 * ### Disabled state
 * Add the `disabled` attribute on the button element or add the modifier class `.rz-button--disabled` to apply the disabled state style.
 */
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
        emphasis: {
            control: { type: 'select' },
            options: [...EMPHASIS, ''],
        },
        size: {
            control: { type: 'select' },
            options: [...SIZES, ''],
        },
        color: {
            control: { type: 'select' },
            options: [...COLORS, ''],
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
        emphasis: 'high',
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
        emphasis: 'high',
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
        emphasis: 'medium',
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
        emphasis: 'medium',
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
        emphasis: 'low',
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
        emphasis: 'low',
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
