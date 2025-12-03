import type { Meta, StoryObj } from '@storybook/html-vite'

export type Args = {
    tag?: string
    innerText?: string
    iconClass?: string
    color?: string
}

const COMPONENT_CLASS_NAME = 'rz-brand-watermark'

const meta: Meta<Args> = {
    title: 'Components/Brand Watermark',
    tags: ['autodocs'],
    args: {
        tag: 'button',
        innerText: 'RZ',
        iconClass: 'rz-icon-rz--logo-rz',
        color: '',
    },
    parameters: {
        layout: 'centered',
    },
}

export default meta
type Story = StoryObj<Args>

function rzBrandWatermarkRenderer(args: Args) {
    const wrapper = document.createElement(args.tag || 'div')
    wrapper.classList.add(COMPONENT_CLASS_NAME)

    if (args.color) {
        wrapper.style.setProperty(
            '--rz-brand-watermark-background-color',
            args.color,
        )
    }

    if (args.innerText) {
        wrapper.innerText = args.innerText
    } else if (args.iconClass) {
        const icon = document.createElement('span')
        icon.className = args.iconClass
        wrapper.appendChild(icon)
    }

    return wrapper
}

export const Default: Story = {
    render: (args) => {
        return rzBrandWatermarkRenderer(args)
    },
}
