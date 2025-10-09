import type { Meta, StoryObj } from '@storybook/html-vite'
import '../app/assets/css/rz-button/rz-button.css'

const EMPHASIS = ['low', 'medium', 'high'] as const
const SIZES = ['xs', 'sm', 'md', 'lg'] as const

type ButtonArgs = {
    label: string
    emphasis: (typeof EMPHASIS)[number]
    size: (typeof SIZES)[number]
    disabled: boolean
    class: string
    iconClass: string
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
 * - `.rz-button--size-lg`
 *
 * ### Disabled state
 * Add the `disabled` attribute on the button element or add the modifier class `.rz-button--disabled` to apply the disabled state style.
 */
const meta: Meta<ButtonArgs> = {
    title: 'Components/RzButton',
    tags: ['autodocs'],
    args: {
        label: 'button label',
        disabled: false,
        iconClass: 'rz-icon-ri--arrow-drop-right-line',
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
    },
}

export default meta
type Story = StoryObj<ButtonArgs>

function buttonRenderer(args: ButtonArgs, attrs: Record<string, string> = {}) {
    const buttonNode = document.createElement('button')
    const attributesEntries = Object.entries(attrs)
    if (attributesEntries.length) {
        attributesEntries.forEach(([key, value]) => {
            buttonNode.setAttribute(key, value)
        })
    }
    const emphasisClass = args.emphasis
        ? `rz-button--emphasis-${args.emphasis}`
        : ''
    const sizeClass = args.size ? `rz-button--size-${args.size}` : ''
    const disabledClass = args.disabled ? `rz-button--disabled` : ''
    buttonNode.className = [
        args.class || 'rz-button',
        emphasisClass,
        sizeClass,
        disabledClass,
    ]
        .join(' ')
        .trim()

    const labelNode = document.createElement('span')
    labelNode.className = ['rz-button__label'].join(' ')
    labelNode.innerText = args.label
    if (args.label) buttonNode.appendChild(labelNode)

    const iconNode = document.createElement('span')
    iconNode.className = ['rz-button__icon', args.iconClass].join(' ')
    if (args.iconClass) buttonNode.appendChild(iconNode)

    return buttonNode
}

export const Default: Story = {
    render: (args) => {
        return buttonRenderer(args)
    },
    parameters: {
        layout: 'centered',
    },
}

export const HighEmphasis: Story = {
    render: (args) => {
        return buttonRenderer(args)
    },
    args: {
        emphasis: 'high',
    },
    parameters: {
        controls: { exclude: ['emphasis', 'class'] },
        layout: 'centered',
    },
}

export const HighEmphasisList: Story = {
    render: (args) => {
        const wrapper = document.createElement('div')
        wrapper.style =
            'display: flex; gap: 16px; flex-wrap: wrap; align-items: center;'

        SIZES.forEach((size) => {
            const btn = buttonRenderer({
                ...args,
                size,
                label: `High emphasis ${size}`,
            })
            wrapper.appendChild(btn)
            const btnIconOnly = buttonRenderer({ ...args, size, label: `` })
            wrapper.appendChild(btnIconOnly)
        })

        return wrapper
    },
    args: {
        emphasis: 'high',
    },
    parameters: {
        controls: { exclude: ['emphasis', 'size', 'label', 'class'] },
    },
}

export const MediumEmphasis: Story = {
    render: (args) => {
        return buttonRenderer(args)
    },
    args: {
        emphasis: 'medium',
    },
    parameters: {
        controls: { exclude: ['emphasis', 'class'] },
        layout: 'centered',
    },
}

export const MediumEmphasisList: Story = {
    render: (args) => {
        const wrapper = document.createElement('div')
        wrapper.style =
            'display: flex; gap: 16px; flex-wrap: wrap; align-items: center;'

        SIZES.forEach((size) => {
            const btn = buttonRenderer({
                ...args,
                size,
                label: `Medium emphasis ${size}`,
            })
            wrapper.appendChild(btn)

            const btnIconOnly = buttonRenderer({ ...args, size, label: `` })
            wrapper.appendChild(btnIconOnly)
        })

        return wrapper
    },
    args: {
        emphasis: 'medium',
    },
    parameters: {
        controls: { exclude: ['emphasis', 'size', 'label', 'class'] },
    },
}

export const LowEmphasis: Story = {
    render: (args) => {
        return buttonRenderer(args)
    },
    args: {
        emphasis: 'low',
    },
    parameters: {
        controls: { exclude: ['emphasis'] },
        layout: 'centered',
    },
}

export const LowEmphasisList: Story = {
    render: (args) => {
        const wrapper = document.createElement('div')
        wrapper.style =
            'display: flex; gap: 16px; flex-wrap: wrap; align-items: center;'

        SIZES.forEach((size) => {
            const btn = buttonRenderer({
                ...args,
                size,
                label: `Low emphasis ${size}`,
            })
            wrapper.appendChild(btn)

            const btnIconOnly = buttonRenderer({ ...args, size, label: `` })
            wrapper.appendChild(btnIconOnly)
        })

        return wrapper
    },
    args: {
        emphasis: 'low',
    },
    parameters: {
        controls: { exclude: ['emphasis', 'size', 'label', 'class'] },
    },
}

export const LiveClassesEditing: Story = {
    render: (args) => {
        return buttonRenderer(args)
    },
    args: {
        emphasis: undefined,
        size: undefined,
        class: 'rz-button rz-button--emphasis-high rz-button--size-lg rz-button--disabled',
    },
    parameters: {
        controls: { exclude: ['emphasis', 'size', 'disabled'] },
        layout: 'centered',
    },
}

export const DisabledList: Story = {
    render: (args) => {
        const rootNode = document.createElement('div')

        ;[true, false].forEach((withDisabledAttr) => {
            const wrapper = document.createElement('div')
            wrapper.style =
                'display: flex; gap: 16px; flex-wrap: wrap; align-items: center; max-width: 800px; margin-inline: auto;'
            const title = document.createElement('h4')
            title.style = 'color: black;'
            title.innerText = withDisabledAttr
                ? 'With disabled attribute'
                : 'With disabled class'
            rootNode.appendChild(title)
            rootNode.appendChild(wrapper)

            EMPHASIS.forEach((emphasis) => {
                SIZES.forEach((size) => {
                    const btn = buttonRenderer(
                        {
                            ...args,
                            size,
                            emphasis,
                            label: `${emphasis} ${size}`,
                            disabled: !withDisabledAttr,
                        },
                        withDisabledAttr ? { disabled: '' } : {},
                    )
                    wrapper.appendChild(btn)

                    const btnIconOnly = buttonRenderer(
                        {
                            ...args,
                            emphasis,
                            size,
                            label: ``,
                            disabled: !withDisabledAttr,
                        },
                        withDisabledAttr ? { disabled: '' } : {},
                    )
                    wrapper.appendChild(btnIconOnly)
                })
            })
        })

        return rootNode
    },
    parameters: {
        controls: {
            include: [],
        },
    },
}
