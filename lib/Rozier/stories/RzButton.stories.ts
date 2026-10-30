import type { Meta, StoryObj } from '@storybook/html-vite'
import {
    COLORS,
    EMPHASIS,
    type RzButtonOptions,
    rzButtonRenderer,
    SIZES,
} from '~/utils/component-renderer/rzButton'

type Args = RzButtonOptions

const meta: Meta<Args> = {
    title: 'Components/Actions/Button',
    tags: ['autodocs'],
    render: (args) => rzButtonRenderer(args),
    args: {
        label: 'button label',
        disabled: false,
        iconClass: 'rz-icon-ri--arrow-drop-right-line',
        onDark: false,
        selected: false,
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
type Story = StoryObj<Args>

export const Default: Story = {}

export const LinkTag: Story = {
    args: {
        tag: 'a',
        attributes: {
            href: '#',
        },
    },
}

export const HighEmphasis: Story = {
    args: {
        emphasis: 'primary',
    },
    parameters: {
        controls: { exclude: ['emphasis'] },
        layout: 'centered',
    },
}

export const HighEmphasisSelected: Story = {
    args: {
        emphasis: 'primary',
        selected: true,
    },
    parameters: {
        controls: { exclude: ['emphasis'] },
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
            exclude: ['emphasis', 'size', 'label'],
        },
    },
}

export const MediumEmphasis: Story = {
    args: {
        emphasis: 'secondary',
    },
    parameters: {
        controls: { exclude: ['emphasis'] },
    },
}

export const MediumEmphasisSelected: Story = {
    args: {
        emphasis: 'secondary',
        selected: true,
    },
    parameters: {
        controls: { exclude: ['emphasis'] },
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
            exclude: ['emphasis', 'size', 'label'],
        },
    },
}

export const LowEmphasis: Story = {
    args: {
        emphasis: 'tertiary',
    },
    parameters: {
        controls: { exclude: ['emphasis'] },
    },
}

export const LowEmphasisSelected: Story = {
    args: {
        emphasis: 'tertiary',
        selected: true,
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
            exclude: ['emphasis', 'size', 'label'],
        },
    },
}

export const LiveClassesEditing: Story = {
    args: {
        emphasis: undefined,
        size: undefined,
        attributes: {
            class: 'rz-button--primary rz-button--lg rz-button--disabled',
        },
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
            exclude: ['emphasis', 'size', 'label'],
        },
    },
}

/* RENDERER */
function buttonSizeListRenderer(args: Args) {
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

/**
 * `tooltip-text` attribute enable tooltip. Popover content is automatically generated by RzButton component
 */
export const Tooltip: Story = {
    args: {
        attributes: {
            is: 'rz-button',
            'tooltip-text': 'This is a tooltip text',
        },
    },
}

export const Pill: Story = {
    args: {
        emphasis: 'primary',
        label: undefined,
        iconClass: 'rz-icon-ri--equalizer-3-line',
        hasPill: true,
    },
}
