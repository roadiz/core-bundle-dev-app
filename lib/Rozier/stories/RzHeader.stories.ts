import type { Meta, StoryObj } from '@storybook/html-vite'
import type { Args as ItemArgs } from './RzHeaderNavItem.stories'
import {
    DEFAULT_NAV_ITEMS,
    rzHeaderRenderer,
} from '~/utils/storybook/renderer/rzHeader'

type NavItem = ItemArgs & {
    children?: ItemArgs[]
    additionalClass?: string
}

export type Args = {
    navItems: NavItem[]
}

const meta: Meta<Args> = {
    title: 'Components/Header',
    tags: ['autodocs'],
    args: {
        navItems: DEFAULT_NAV_ITEMS,
    },
}

export default meta
type Story = StoryObj<Args>

export const Default: Story = {
    render: (args) => {
        return rzHeaderRenderer(args)
    },
}
