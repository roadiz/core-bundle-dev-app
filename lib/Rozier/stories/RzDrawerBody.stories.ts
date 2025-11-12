import type { Meta, StoryObj } from '@storybook/html-vite'
import type { Args as DrawerItemArgs } from './RzDrawerItem.stories'
import { rzDrawerItemRenderer } from '~/utils/storybook/renderer/rzDrawerItem'
import image from './assets/images/01.jpg'

const COMPONENT_CLASS_NAME = 'rz-drawer-body'

type Args = {
    ariaLabel?: string
    items: DrawerItemArgs[]
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
        src: image,
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
        items: [...Array(10).keys()].map(() => SIMPLE_ITEM),
    },
}

export default meta
type Story = StoryObj<Args>

function rzDrawerBodyRenderer(args: Args) {
    const wrapper = document.createElement('div')
    wrapper.classList.add(COMPONENT_CLASS_NAME)

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
    args: {},
}

export const OneItem: Story = {
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
    },
}

export const OneImage: Story = {
    render: (args) => {
        return rzDrawerBodyRenderer(args)
    },
    args: {
        items: [IMAGE_ITEM],
    },
}
