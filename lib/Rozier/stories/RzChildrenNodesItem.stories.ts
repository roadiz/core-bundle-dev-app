import type { Meta, StoryObj } from '@storybook/html-vite'
import {
    type Args,
    rzChildrenNodesItemRenderer,
    defaultItemData,
    MODIFIERS,
} from '~/utils/storybook/renderer/rzChildrenNodesItem'

const meta: Meta<Args> = {
    title: 'Components/Form/ChildrenNodesWidget/Item',
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
        return rzChildrenNodesItemRenderer(args)
    },
}
