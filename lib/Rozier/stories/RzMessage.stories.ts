import type { Meta, StoryObj } from '@storybook/html-vite'
import { rzMessageRenderer } from '../app/utils/storybook/renderer/rzMessage'

/* Think about accessibility during integration, e.g., role="alert" when creating error messages */
const COLORS = ['error'] as const

export type Args = {
    text: string
    color?: (typeof COLORS)[number]
}

const meta: Meta<Args> = {
    title: 'Components/Message',
    args: {
        text: 'Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Aenean lacinia bibendum nulla sed consectetur. Lorem ipsum dolor sit amet, consectetur',
    },
    argTypes: {
        color: {
            options: ['', ...COLORS],
            control: { type: 'radio' },
        },
    },
}

export default meta
type Story = StoryObj<Args>

export const Default: Story = {
    render: (args) => {
        return rzMessageRenderer(args)
    },
}

export const WithMoreContent: Story = {
    render: (args) => {
        const container = document.createElement('div')
        container.style.display = 'flex'
        container.style.flexDirection = 'column'
        container.style.gap = '1rem'

        const head = document.createElement('div')
        head.style = `
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        `

        const icon = document.createElement('span')
        icon.classList.add('rz-icon-ri--close-line')
        head.appendChild(icon)

        const title = document.createElement('span')
        title.classList.add(`text-form-label`)
        title.textContent = 'Error title'
        head.appendChild(title)

        const message1 = rzMessageRenderer(args)
        message1.insertBefore(head, message1.firstChild)
        container.appendChild(message1)

        const message2 = rzMessageRenderer(args)
        if (message2.lastChild instanceof HTMLElement) {
            message2.lastChild.style.display = 'inline'
        }
        const icon2 = icon.cloneNode(true) as HTMLElement
        icon2.style.translate = '0 0.2lh'
        icon2.style.marginRight = '0.3rem'
        message2.insertBefore(icon2, message2.firstChild)
        container.appendChild(message2)

        return container
    },
    args: {
        color: 'error',
    },
}
