import type { Meta, StoryObj } from '@storybook/html-vite'
import type { Args as DrawerItemArgs } from './RzDrawerItem.stories'
import {
    rzFormFieldRenderer,
    type Args as FormFieldArgs,
} from '~/utils/storybook/renderer/rzFormField'
import { rzDrawerItemRenderer } from '~/utils/storybook/renderer/rzDrawerItem'
// @ts-expect-error — image module declaration not recognized
import imageHorizontal from './assets/images/01.jpg'
// @ts-expect-error — image module declaration not recognized
import imageVertical from './assets/images/02.jpg'

type Args = {
    ariaLabel?: string
    items: DrawerItemArgs[]
    moreColumns?: boolean
    formField: FormFieldArgs
}

const COMPONENT_CLASS_NAME = 'rz-drawer'

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
    title: 'Components/Drawer',
    tags: ['autodocs'],
    args: {
        items: [...Array(10).keys()].map(() => DEFAULT_ITEM),
        moreColumns: false,
        formField: {
            label: 'Drawer item label',
            iconClass: 'rz-icon-ri--image-line',
            input: undefined,
            badge: {
                label: '0/255',
                color: 'error',
                size: 'xs',
            },
            buttonGroup: {
                size: 'md',
                gap: 'md',
                buttons: [
                    {
                        label: 'Upload',
                        iconClass: 'rz-icon-ri--upload-line',
                        size: 'sm',
                    },
                    {
                        label: 'Explore',
                        iconClass: 'rz-icon-ri--add-line',
                        size: 'sm',
                    },
                ],
            },
        },
    },
}

export default meta
type Story = StoryObj<Args>

function rzDrawerRenderer(args: Args) {
    const wrapper = document.createElement('div')
    wrapper.classList.add(COMPONENT_CLASS_NAME)
    if (args.moreColumns)
        wrapper.classList.add(`${COMPONENT_CLASS_NAME}--more-columns`)

    const head = rzFormFieldRenderer(args.formField)
    head.classList.add(`${COMPONENT_CLASS_NAME}__head`)
    wrapper.appendChild(head)

    const body = document.createElement('div')
    body.classList.add(`${COMPONENT_CLASS_NAME}__body`)
    wrapper.appendChild(body)

    args.items.forEach((itemArgs) => {
        const itemNode = rzDrawerItemRenderer(itemArgs)
        body.appendChild(itemNode)
    })

    return wrapper
}

export const Default: Story = {
    render: (args) => {
        return rzDrawerRenderer(args)
    },
}

export const OneDefault: Story = {
    render: (args) => {
        return rzDrawerRenderer(args)
    },
    args: {
        items: [DEFAULT_ITEM],
    },
}

export const SimpleItems: Story = {
    render: (args) => {
        return rzDrawerRenderer(args)
    },
    args: {
        items: [...Array(10).keys()].map(() => SIMPLE_ITEM),
    },
}

export const SimpleItem: Story = {
    render: (args) => {
        return rzDrawerRenderer(args)
    },
    args: {
        items: [SIMPLE_ITEM],
    },
}

export const Images: Story = {
    render: (args) => {
        return rzDrawerRenderer(args)
    },
    args: {
        items: [...Array(10).keys()].map(() => IMAGE_ITEM),
        moreColumns: true,
    },
}

export const Image: Story = {
    render: (args) => {
        return rzDrawerRenderer(args)
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
        return rzDrawerRenderer(args)
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
        return rzDrawerRenderer(args)
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
