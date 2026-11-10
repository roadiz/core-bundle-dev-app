import type { Meta, StoryObj } from '@storybook/html-vite'
import {
    type RzSwitchOptions,
    rzSwitchRenderer,
} from '~/utils/storybook/renderer/rzSwitch'

type Args = RzSwitchOptions

const meta: Meta<Args> = {
    title: 'Components/Form/Switch',
    tags: ['autodocs'],
    render: (args) => rzSwitchRenderer(args),
    args: {
        checked: false,
    },
}

export default meta
type Story = StoryObj<Args>

export const Default: Story = {}

export const Checked: Story = {
    args: {
        checked: true,
    },
}
