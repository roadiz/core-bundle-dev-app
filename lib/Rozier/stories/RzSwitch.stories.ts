import type { Meta, StoryObj } from '@storybook/html-vite'

const COMPONENT_CLASS = 'rz-switch'
const COMPONENT_CLASS_NAME = 'rz-switch'

type Args = {
    checked: boolean
}

const meta: Meta<Args> = {
    title: 'Components/Form/Switch',
    args: {
        checked: false,
    },
}

export default meta
type Story = StoryObj<Args>

function itemRenderer(args: Args) {
    const input = document.createElement('button', { is: COMPONENT_CLASS })
    input.setAttribute('is', COMPONENT_CLASS)

    input.classList.add(COMPONENT_CLASS_NAME)
    input.setAttribute('aria-checked', (args.checked ?? false).toString())

    return input
}

export const Default: Story = {
    render: (args) => {
        return itemRenderer(args)
    },
}
