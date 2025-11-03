import type { Meta, StoryObj } from '@storybook/html-vite'

const COMPONENT_CLASS_NAME = 'rz-switch'

type Args = {
    checked: boolean
}

const meta: Meta<Args> = {
    title: 'Components/Form/Switch',
    tags: ['autodocs'],
    args: {
        checked: false,
    },
}

export default meta
type Story = StoryObj<Args>

function itemRenderer(args: Args) {
    const input = document.createElement('input')
    input.setAttribute('type', 'checkbox')
    input.classList.add(COMPONENT_CLASS_NAME)

    if (args.checked) input.setAttribute('checked', '')
    else input.removeAttribute('checked')

    return input
}

export const Default: Story = {
    render: (args) => {
        return itemRenderer(args)
    },
}
