import type { Meta, StoryObj } from '@storybook/html-vite'
import type { Args as DrawerItemArgs } from './RzDrawerItem.stories'
import { rzDrawerItemRenderer } from '../app/utils/storybook/renderer/rzDrawerItem'
import imageHorizontal from './assets/images/01.jpg'
import imageVertical from './assets/images/02.jpg'

const COMPONENT_CLASS_NAME = 'rz-drawer-body'

type Args = {
    ariaLabel?: string
    items: DrawerItemArgs[]
    moreColumns?: boolean
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
    title: 'Components/Drawer/body',
    tags: ['autodocs'],
    args: {
        items: [...Array(10).keys()].map(() => DEFAULT_ITEM),
        moreColumns: false,
    },
}

export default meta
type Story = StoryObj<Args>

function rzDrawerBodyRenderer(args: Args) {
    const wrapper = document.createElement('div')
    wrapper.classList.add(COMPONENT_CLASS_NAME)
    if (args.moreColumns)
        wrapper.classList.add(`${COMPONENT_CLASS_NAME}--more-columns`)

    args.items.forEach((itemArgs) => {
        const itemNode = rzDrawerItemRenderer(itemArgs)
        wrapper.appendChild(itemNode)
    })

    return wrapper
}

export const Default: Story = {
    render: (args) => {
        return rzDrawerBodyRenderer(args)
    },
}

export const OneDefault: Story = {
    render: (args) => {
        return rzDrawerBodyRenderer(args)
    },
    args: {
        items: [DEFAULT_ITEM],
    },
}

export const SimpleItems: Story = {
    render: (args) => {
        return rzDrawerBodyRenderer(args)
    },
    args: {
        items: [...Array(10).keys()].map(() => SIMPLE_ITEM),
    },
}

export const SimpleItem: Story = {
    render: (args) => {
        return rzDrawerBodyRenderer(args)
    },
    args: {
        items: [SIMPLE_ITEM],
    },
}

export const Images: Story = {
    render: (args) => {
        return rzDrawerBodyRenderer(args)
    },
    args: {
        items: [...Array(10).keys()].map(() => IMAGE_ITEM),
        moreColumns: true,
    },
}

export const Image: Story = {
    render: (args) => {
        return rzDrawerBodyRenderer(args)
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
        moreColumns: true,
    },
}

export const WithPictures: Story = {
    render: (args) => {
        return rzDrawerBodyRenderer(args)
    },
    args: {
        moreColumns: true,
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
        return rzDrawerBodyRenderer(args)
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
