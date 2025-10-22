import type { Meta, StoryObj } from '@storybook/html-vite'

const COMPONENT_CLASS = 'rz-switch'

type Args = {
    checked: boolean
}

const meta: Meta<Args> = {
    title: 'Components/RzForm/Switch',
    args: {
        checked: false,
    },
    globals: {
        backgrounds: { value: 'light' },
    },
}

export default meta
type Story = StoryObj<Args>

function itemRenderer(args: Args) {
    const componentClass = 'rz-switch'
    const input = document.createElement('button', { is: componentClass })
    input.setAttribute('is', componentClass)

    input.classList.add(COMPONENT_CLASS)
    input.setAttribute('type', 'button')
    input.setAttribute('role', 'switch')
    input.setAttribute('aria-checked', (args.checked ?? false).toString())

    return input
}

export const Default: Story = {
    render: (args) => {
        return itemRenderer(args)
    },
}
