import type { Meta, StoryObj } from '@storybook/html-vite'
import {
    rzDrawerRenderer,
    DRAWER_LAYOUTS,
    defaultFormFieldData,
    type RzDrawerArgs,
} from '~/utils/storybook/renderer/rzDrawer'
import {
    rzCardItemDrawerRenderer,
    type Args as DrawerItemArgs,
} from '~/utils/storybook/renderer/rzCardItemDrawer'
// @ts-expect-error — image module declaration not recognized
import imageHorizontal from './assets/images/01.jpg'
// @ts-expect-error — image module declaration not recognized
import imageVertical from './assets/images/02.jpg'

export type Args = RzDrawerArgs & {
    items: DrawerItemArgs[]
}

const DEFAULT_ITEM: DrawerItemArgs = {
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

const SIMPLE_ITEM: DrawerItemArgs = {
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

const IMAGE_ITEM: DrawerItemArgs = {
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
    title: 'Components/Drawer/Card/Root',
    tags: ['autodocs'],
    args: {
        items: [...Array(10).keys()].map(() => DEFAULT_ITEM),
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

function rzDrawerDefaultRenderer(args: Args) {
    const { wrapper, body } = rzDrawerRenderer(args)

    args.items.forEach((itemArgs) => {
        const itemNode = rzCardItemDrawerRenderer(itemArgs)
        body.appendChild(itemNode)
    })

    return wrapper
}

export const Default: Story = {
    render: (args) => {
        return rzDrawerDefaultRenderer(args)
    },
}

export const OneDefault: Story = {
    render: (args) => {
        return rzDrawerDefaultRenderer(args)
    },
    args: {
        items: [DEFAULT_ITEM],
    },
}

export const SimpleItems: Story = {
    render: (args) => {
        return rzDrawerDefaultRenderer(args)
    },
    args: {
        items: [...Array(10).keys()].map(() => SIMPLE_ITEM),
    },
}

export const SimpleItem: Story = {
    render: (args) => {
        return rzDrawerDefaultRenderer(args)
    },
    args: {
        items: [SIMPLE_ITEM],
    },
}

export const Images: Story = {
    render: (args) => {
        return rzDrawerDefaultRenderer(args)
    },
    args: {
        items: [...Array(10).keys()].map(() => IMAGE_ITEM),
        layout: 'grid-larger',
    },
}

export const Image: Story = {
    render: (args) => {
        return rzDrawerDefaultRenderer(args)
    },
    args: {
        items: [
            IMAGE_ITEM,
            {
                ...IMAGE_ITEM,
                image: {
                    src: imageVertical,
                    width: 88,
                    height: 110,
                },
            },
        ],
        layout: 'grid-larger',
    },
}

export const WithPictures: Story = {
    render: (args) => {
        return rzDrawerDefaultRenderer(args)
    },
    args: {
        layout: 'grid-larger',
        items: [
            {
                ...IMAGE_ITEM,
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
                ...IMAGE_ITEM,
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
        ],
    },
}

export const Mixed: Story = {
    render: (args) => {
        return rzDrawerDefaultRenderer(args)
    },
    args: {
        items: [
            DEFAULT_ITEM,
            SIMPLE_ITEM,
            SIMPLE_ITEM,
            DEFAULT_ITEM,
            DEFAULT_ITEM,
            SIMPLE_ITEM,
            SIMPLE_ITEM,
            SIMPLE_ITEM,
            SIMPLE_ITEM,
        ],
    },
}
