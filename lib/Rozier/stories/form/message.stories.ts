import type { Meta, StoryObj } from '@storybook/html-vite'

const COMPONENT_CLASS_NAME = 'rz-form-message'
const TYPES = ['error'] as const

type Args = {
    text: string
    type: (typeof TYPES)[number]
}

const meta: Meta<Args> = {
    title: 'Components/RzForm/Message',
    args: {
        text: 'Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Aenean lacinia bibendum nulla sed consectetur. Lorem ipsum dolor sit amet, consectetur',
    },
    argTypes: {
        type: {
            options: ['', ...TYPES],
            control: { type: 'radio' },
        },
    },
}

export default meta
type Story = StoryObj<Args>

function itemRenderer(args: Args) {
    const wrapper = document.createElement('div')
    const classList = [
        COMPONENT_CLASS_NAME,
        args.type ? `${COMPONENT_CLASS_NAME}--type-${args.type}` : '',
    ].filter((c) => c)
    wrapper.classList.add(...classList)

    if (args.type === 'error') {
        /* If needed, think about accessibility during integration */
        wrapper.setAttribute('role', 'alert')
    }

    const text = document.createElement('p')
    text.classList.add('text-form-supporting-text')
    text.style.margin = '0'
    text.textContent = args.text
    wrapper.appendChild(text)

    return wrapper
}

export const Default: Story = {
    render: (args) => {
        return itemRenderer(args)
    },
}

export const WithMoreContent: Story = {
    render: (args) => {
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

        const wrapper = itemRenderer(args)
        wrapper.insertBefore(head, wrapper.firstChild)
        return wrapper
    },
    args: {
        type: 'error',
    },
}
