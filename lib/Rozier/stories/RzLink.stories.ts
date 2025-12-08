import type { Meta, StoryObj } from '@storybook/html-vite'
import { ATTRIBUTES_OPTIONS_MAP } from '~/utils/Tooltip'

type Attributes = {
    [key: string]: string
    href?: string
    target?: string
    rel?: string
    is?: string
} & Partial<typeof ATTRIBUTES_OPTIONS_MAP>

export type Args = {
    label?: string
    attributes?: Attributes
}

const meta: Meta<Args> = {
    title: 'Components/Link',
    tags: ['autodocs'],
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
    const attrs = args.attributes ? Object.entries(args.attributes) : []

    if (attrs.length) {
        attrs.forEach(([key, value]) => {
            el.setAttribute(key, value)
        })
    }

    if (args.label) {
        el.textContent = args.label
    }

    return el
}

export const Default: Story = {
    render: (args) => {
        return rzLinkRenderer(args)
    },
}

export const TooltipLinkButton: Story = {
    render: (args) => {
        const link = rzLinkRenderer(args)
        link.classList.add('rz-button')

        return link
    },
    args: {
        attributes: {
            is: 'rz-link',
            href: '#',
            'tooltip-text': 'I am a tooltip on a link button',
            'popover-placement': 'top',
            'popover-offset': '4',
        },
    },
}
