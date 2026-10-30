import type { Meta, StoryObj } from '@storybook/html-vite'
import {
    DEFAULT_NAV_ITEMS,
    type RzHeaderOptions,
    rzHeaderRenderer,
} from '~/utils/storybook/renderer/rzHeader'

type Args = RzHeaderOptions

const meta: Meta<Args> = {
    title: 'Patterns/Header',
    tags: ['autodocs'],
    render: (args) => rzHeaderRenderer(args),
    args: {
        navItems: DEFAULT_NAV_ITEMS,
    },
}

export default meta
type Story = StoryObj<Args>

export const Default: Story = {}
