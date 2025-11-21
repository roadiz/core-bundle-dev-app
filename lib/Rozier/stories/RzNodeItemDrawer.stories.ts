import type { Meta, StoryObj } from '@storybook/html-vite'
import {
    type Args,
    rzNodeItemDrawerRenderer,
    defaultItemData,
    MODIFIERS,
} from '~/utils/storybook/renderer/rzNodeItemDrawer'

const meta: Meta<Args> = {
    title: 'Components/Drawer/Node/Item',
    tags: ['autodocs'],
    args: {
        ...defaultItemData,
    },
    argTypes: {
        modifiers: {
            options: MODIFIERS,
            control: {
                type: 'inline-check',
            },
        },
    },
}

export default meta
type Story = StoryObj<Args>

export const Default: Story = {
    render: (args) => {
        return rzNodeItemDrawerRenderer(args)
    },
}
