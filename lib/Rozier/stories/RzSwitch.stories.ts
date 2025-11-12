import type { Meta, StoryObj } from '@storybook/html-vite'
import { rzSwitchRenderer } from '../app/utils/storybook/renderer/rzSwitch'

export type Args = {
    checked: boolean
    attributes?: { [key: string]: string }
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

export const Default: Story = {
    render: (args) => {
        return rzSwitchRenderer(args)
    },
}

export const Checked: Story = {
    render: (args) => {
        return rzSwitchRenderer(args)
    },
    args: {
        checked: true,
    },
}
