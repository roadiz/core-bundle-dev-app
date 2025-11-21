import type { Meta, StoryObj } from '@storybook/html-vite'
import {
    rzDrawerRenderer,
    DRAWER_LAYOUTS,
    defaultFormFieldData,
    type RzDrawerArgs,
} from '~/utils/storybook/renderer/rzDrawer'

export type Args = RzDrawerArgs

const meta: Meta<Args> = {
    title: 'Components/Drawer/Skeleton',
    tags: ['autodocs'],
    args: {
        layout: 'grid',
        formField: defaultFormFieldData,
    },
    argTypes: {
        layout: {
            control: { type: 'select' },
            options: DRAWER_LAYOUTS,
        },
    },
}

export default meta
type Story = StoryObj<Args>

export const Default: Story = {
    render: (args) => {
        const { wrapper, body } = rzDrawerRenderer(args)

        Array.from(Array(6).keys()).forEach(() => {
            const item = document.createElement('div')
            item.style = `
                width: 100%;
                height: 100px;
                background-color: #e0e0e0;
                border: 1px solid #ccc;
                border-radius: 4px;
            `
            body.appendChild(item)
        })

        return wrapper
    },
}
