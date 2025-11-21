import type { Meta, StoryObj } from '@storybook/html-vite'
import {
    rzDrawerRenderer,
    defaultFormFieldData,
    type RzDrawerArgs,
} from '~/utils/storybook/renderer/rzDrawer'
import {
    type Args as ItemArgs,
    defaultItemData,
    rzNodeItemDrawerRenderer,
} from '~/utils/storybook/renderer/rzNodeItemDrawer'

export type Args = RzDrawerArgs & {
    items: ItemArgs[]
}

const meta: Meta<Args> = {
    title: 'Components/Drawer/Node/Root',
    tags: ['autodocs'],
    args: {
        formField: {
            ...defaultFormFieldData,
            badge: undefined,
            description: undefined,
            label: 'Blocs',
            iconClass: 'rz-icon-ri--layout-4-line',
            buttonGroup: {
                size: 'md',
                gap: 'md',
                buttons: [
                    {
                        label: 'Add bloc',
                        iconClass: 'rz-icon-ri--add-line',
                    },
                ],
            },
        },
        items: [defaultItemData, defaultItemData, defaultItemData],
        layout: 'full',
    },
}

export default meta
type Story = StoryObj<Args>
function rzNodeDrawerRenderer(args: Args) {
    const { wrapper, body } = rzDrawerRenderer(args)

    args.items.forEach((itemArgs) => {
        const itemNode = rzNodeItemDrawerRenderer(itemArgs)
        body.appendChild(itemNode)
    })

    return wrapper
}

export const Default: Story = {
    render: (args) => {
        return rzNodeDrawerRenderer(args)
    },
}
