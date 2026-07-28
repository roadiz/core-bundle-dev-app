import type { Meta, StoryObj } from '@storybook/html-vite'
import {
    type Args as RzNodeIconArgs,
    rzNodeIconRenderer,
} from '~/utils/storybook/renderer/rzNodeIcon'

type Args = RzNodeIconArgs

const meta: Meta<Args> = {
    title: 'Components/NodeIcon',
    tags: ['autodocs'],
    args: {
        nodeId: '42',
        status: 'published',
        size: 'small',
    },
    argTypes: {
        status: {
            options: ['published', 'draft'],
            control: { type: 'radio' },
            description: 'Node publication status.',
        },
        size: {
            options: ['small', 'medium'],
            control: { type: 'radio' },
            description:
                'Icon size. Medium size displays a thumbnail placeholder.',
        },
        color: {
            control: { type: 'color' },
            description: 'Custom color for the node icon background.',
        },
        nodeId: {
            control: { type: 'text' },
            description: 'Node ID used for fetching the thumbnail.',
        },
    },
    parameters: {
        layout: 'centered',
    },
    decorators: [
        (story, context) => {
            const container = document.createElement('div')
            container.style.display = 'flex'
            container.style.alignItems = 'center'
            container.style.justifyContent = 'center'
            container.style.padding = '1rem'

            const element = story() as HTMLElement
            const sizeValue = context.args.size === 'medium' ? '48px' : '12px'
            element.style.width = sizeValue
            element.style.height = sizeValue

            container.appendChild(element)
            return container
        },
    ],
}

export default meta
type Story = StoryObj<Args>

export const Default: Story = {
    render: (args) => {
        return rzNodeIconRenderer(args)
    },
}

export const Published: Story = {
    render: (args) => {
        return rzNodeIconRenderer(args)
    },
    args: {
        status: 'published',
    },
}

export const Draft: Story = {
    render: (args) => {
        return rzNodeIconRenderer(args)
    },
    args: {
        status: 'draft',
    },
}

export const MediumSize: Story = {
    render: (args) => {
        return rzNodeIconRenderer(args)
    },
    args: {
        size: 'medium',
        status: 'published',
    },
}

export const MediumDraft: Story = {
    render: (args) => {
        return rzNodeIconRenderer(args)
    },
    args: {
        size: 'medium',
        status: 'draft',
    },
}

export const CustomColor: Story = {
    render: (args) => {
        return rzNodeIconRenderer(args)
    },
    args: {
        color: '#3498db',
        status: 'published',
    },
}

export const MediumCustomColor: Story = {
    render: (args) => {
        return rzNodeIconRenderer(args)
    },
    args: {
        size: 'medium',
        color: 'salmon',
        status: 'published',
    },
}
