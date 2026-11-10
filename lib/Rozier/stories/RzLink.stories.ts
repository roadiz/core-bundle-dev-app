import type { Meta, StoryObj } from '@storybook/html-vite'
import { ATTRIBUTES_OPTIONS_MAP } from '~/utils/Tooltip'

type Attributes = {
    [key: string]: string
    href?: string
    target?: string
    rel?: string
    is?: string
} & Partial<typeof ATTRIBUTES_OPTIONS_MAP>

type Args = {
    label?: string
    attributes?: Attributes
}

const meta: Meta<Args> = {
    title: 'Components/Navigation/Link',
    tags: ['autodocs'],
    render: (args) => rzLinkRenderer(args),
    args: {
        label: 'My link',
        attributes: {
            href: '#',
        },
    },
    parameters: {
        layout: 'centered',
    },
}

export default meta
type Story = StoryObj<Args>

function rzLinkRenderer(args: Args) {
    const is = args.attributes?.is
    const el = document.createElement('a', is ? { is } : undefined)
    const attributesEntries = args.attributes
        ? Object.entries(args.attributes)
        : []

    if (attributesEntries.length) {
        attributesEntries.forEach(([key, value]) => {
            el.setAttribute(key, value)
        })
    }

    if (args.label) {
        el.textContent = args.label
    }

    return el
}

export const Default: Story = {}

export const TooltipLinkButton: Story = {
    args: {
        attributes: {
            is: 'rz-link',
            class: 'rz-button',
            href: '#',
            'tooltip-text': 'I am a tooltip on a link button',
            'popover-placement': 'top',
            'popover-offset': '4',
        },
    },
}
