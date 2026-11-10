import type { Meta, StoryObj } from '@storybook/html-vite'
import {
    defaultItemData,
    MODIFIERS,
    type RzChildrenNodesItemOptions,
    rzChildrenNodesItemRenderer,
} from '~/utils/storybook/renderer/rzChildrenNodesItem'

type Args = RzChildrenNodesItemOptions

const meta: Meta<Args> = {
    title: 'Components/Form/ChildrenNodes/Item',
    tags: ['autodocs'],
    render: (args) => rzChildrenNodesItemRenderer(args),
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

export const Default: Story = {}
