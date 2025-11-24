import type { Meta, StoryObj } from '@storybook/html-vite'
import {
    rzDrawerRenderer,
    DRAWER_LAYOUTS,
    defaultFormFieldData,
    type RzDrawerArgs,
} from '~/utils/storybook/renderer/rzDrawer'
import {
    rzDrawerItemRenderer,
    type Args as DrawerItemArgs,
} from '~/utils/storybook/renderer/rzDrawerItem'
// @ts-expect-error — image module declaration not recognized
import imageHorizontal from './assets/images/01.jpg'
// @ts-expect-error — image module declaration not recognized
import imageVertical from './assets/images/02.jpg'

export type Args = RzDrawerArgs

const NODE_WITH_IMG_ITEM: DrawerItemArgs = {
    overtitle: 'Overtitle example',
    title: 'Title example',
    image: {
        src: imageVertical,
        width: 110,
        height: 94,
    },
    buttonGroup: {
        gap: 'sm',
        size: 'sm',
        buttons: [
            {
                iconClass: 'rz-icon-ri--equalizer-3-line',
                emphasis: 'primary',
            },
            {
                iconClass: 'rz-icon-ri--delete-bin-7-line',
                color: 'error-light',
            },
        ],
    },
}

const NODE_ITEM: DrawerItemArgs = {
    overtitle: 'Overtitle example',
    title: 'Title example',
    buttonGroup: {
        gap: 'sm',
        size: 'sm',
        buttons: [
            {
                iconClass: 'rz-icon-ri--equalizer-3-line',
                emphasis: 'primary',
            },
            {
                iconClass: 'rz-icon-ri--delete-bin-7-line',
                color: 'error-light',
            },
        ],
    },
}

const DOCUMENT_ITEM: DrawerItemArgs = {
    image: {
        src: imageHorizontal,
        width: 110,
        height: 94,
    },
    buttonGroup: {
        gap: 'sm',
        size: 'sm',
        buttons: [
            {
                iconClass: 'rz-icon-ri--equalizer-3-line',
                emphasis: 'primary',
            },
            {
                iconClass: 'rz-icon-ri--delete-bin-7-line',
                color: 'error-light',
            },
        ],
    },
    buttonGroupTop: {
        gap: 'sm',
        size: 'sm',
        buttons: [
            {
                iconClass: 'rz-icon-ri--zoom-in-line',
                emphasis: 'primary',
            },
        ],
    },
}

const meta: Meta<Args> = {
    title: 'Components/Form/Drawer/Root',
    tags: ['autodocs'],
    args: {
        ...defaultFormFieldData,
        items: [...Array(10).fill(null)],
        layout: 'grid',
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

function rzDrawerDefaultRenderer(args: Args) {
    const { wrapper, body } = rzDrawerRenderer(args)

    args.items.forEach((itemArgs) => {
        if (itemArgs === null) {
            const item = document.createElement('div')
            item.style = `
                width: 100%;
                height: 100px;
                background-color: #e0e0e0;
                border: 1px solid #ccc;
                border-radius: 4px;
            `
            body.appendChild(item)
        } else {
            const itemNode = rzDrawerItemRenderer(itemArgs)
            body.appendChild(itemNode)
        }
    })

    return wrapper
}

export const Default: Story = {
    render: (args) => {
        return rzDrawerDefaultRenderer(args)
    },
}

export const NodeEntityDrawer: Story = {
    render: (args) => {
        return rzDrawerDefaultRenderer(args)
    },
    args: {
        items: [...Array(4).fill(null)].map(() => NODE_WITH_IMG_ITEM),
        layout: 'grid',
    },
}

export const NodeEntityWithoutImgDrawer: Story = {
    render: (args) => {
        return rzDrawerDefaultRenderer(args)
    },
    args: {
        items: [...Array(10).fill(null)].map(() => NODE_ITEM),
        layout: 'grid',
    },
}

export const NodeEntityMixed: Story = {
    render: (args) => {
        return rzDrawerDefaultRenderer(args)
    },
    args: {
        items: [
            NODE_ITEM,
            NODE_WITH_IMG_ITEM,
            NODE_WITH_IMG_ITEM,
            NODE_ITEM,
            NODE_ITEM,
            NODE_ITEM,
            NODE_WITH_IMG_ITEM,
            NODE_ITEM,
            NODE_WITH_IMG_ITEM,
        ],
        layout: 'grid',
    },
}

export const DocumentDrawer: Story = {
    render: (args) => {
        return rzDrawerDefaultRenderer(args)
    },
    args: {
        items: [...Array(10).fill(null)].map(() => DOCUMENT_ITEM),
        layout: 'grid-larger',
    },
}

export const DocumentWithPictureTemplateDrawer: Story = {
    render: (args) => {
        return rzDrawerDefaultRenderer(args)
    },
    args: {
        layout: 'grid-larger',
        items: [
            {
                ...DOCUMENT_ITEM,
                image: {
                    src: imageHorizontal,
                    width: 110,
                    height: 94,
                    sources: [
                        {
                            type: 'image/webp',
                            srcset: imageVertical,
                        },
                    ],
                },
            },
            {
                ...DOCUMENT_ITEM,
                image: {
                    src: imageVertical,
                    width: 110,
                    height: 94,
                    sources: [
                        {
                            type: 'image/webp',
                            srcset: imageHorizontal,
                        },
                    ],
                },
            },
            DOCUMENT_ITEM,
            DOCUMENT_ITEM,
        ],
    },
}
